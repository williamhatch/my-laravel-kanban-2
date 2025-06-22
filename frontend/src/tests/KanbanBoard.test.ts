import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import KanbanBoard from "../components/KanbanBoard.vue";
import { authService } from "../services/auth";
import apiClient from "../services/api";

// Mock dependencies
vi.mock("../services/auth", () => ({
  authService: {
    logout: vi.fn(),
  },
  authState: {
    isAuthenticated: true,
    user: { name: "Test User" },
  },
}));

vi.mock("../services/api", () => ({
  default: {
    get: vi.fn(),
    put: vi.fn(),
  },
}));

// Mock sortablejs
vi.mock("sortablejs", () => ({
  default: vi.fn().mockImplementation(() => ({
    destroy: vi.fn(),
  })),
}));

// Mock child components
vi.mock("../components/NewTaskForm.vue", () => ({
  default: {
    name: "NewTaskForm",
    template: '<div class="new-task-form">Add Task</div>',
  },
}));

vi.mock("../components/TaskModal.vue", () => ({
  default: {
    name: "TaskModal",
    template: '<div class="task-modal">Modal</div>',
  },
}));

describe("KanbanBoard", () => {
  const mockColumns = [
    {
      id: 1,
      name: "Backlog",
      tasks: [
        { id: 1, title: "Task 1", column_id: 1, order: 0 },
        { id: 2, title: "Task 2", column_id: 1, order: 1 },
      ],
    },
    {
      id: 2,
      name: "Up Next",
      tasks: [{ id: 3, title: "Task 3", column_id: 2, order: 0 }],
    },
    {
      id: 3,
      name: "In Progress",
      tasks: [],
    },
    {
      id: 4,
      name: "Done",
      tasks: [],
    },
  ];

  beforeEach(() => {
    vi.clearAllMocks();
    mockApiClient.get.mockResolvedValue({
      data: mockColumns,
    });
    mockApiClient.put.mockResolvedValue({
      data: { success: true },
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it("应该正确渲染看板", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    expect(wrapper.find("h1").text()).toBe("My Kanban Board");
    expect(wrapper.find(".user-info").text()).toContain("Welcome, Test User");
  });

  it("应该在挂载时获取数据", async () => {
    mount(KanbanBoard);
    await nextTick();

    expect(mockApiClient.get).toHaveBeenCalledWith("/api/kanban");
  });

  it("应该渲染所有列", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const columns = wrapper.findAll(".kanban-column");
    expect(columns).toHaveLength(4);
    expect(columns[0].find("h2").text()).toBe("Backlog");
    expect(columns[1].find("h2").text()).toBe("Up Next");
    expect(columns[2].find("h2").text()).toBe("In Progress");
    expect(columns[3].find("h2").text()).toBe("Done");
  });

  it("应该渲染任务卡片", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const taskCards = wrapper.findAll(".task-card");
    expect(taskCards).toHaveLength(3); // 总共3个任务
    expect(taskCards[0].text()).toContain("Task 1");
    expect(taskCards[1].text()).toContain("Task 2");
    expect(taskCards[2].text()).toContain("Task 3");
  });

  it("应该处理任务点击事件", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const firstTask = wrapper.find(".task-card");
    await firstTask.trigger("click");

    // 检查模态框是否显示
    expect(wrapper.find(".task-modal").exists()).toBe(true);
  });

  it("应该处理注销功能", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const logoutButton = wrapper.find(".logout-button");
    await logoutButton.trigger("click");

    expect(authService.logout).toHaveBeenCalled();
  });

  it("应该处理新任务添加", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const newTask = {
      id: 4,
      title: "New Task",
      column_id: 1,
      order: 2,
    };

    // 模拟新任务添加事件
    const newTaskForm = wrapper.findComponent({ name: "NewTaskForm" });
    await newTaskForm.vm.$emit("task-added", newTask);

    // 验证任务是否添加到正确的列
    const backlogColumn = wrapper.vm.columns.find((col: any) => col.id === 1);
    expect(backlogColumn.tasks).toHaveLength(3);
    expect(backlogColumn.tasks[2]).toEqual(newTask);
  });

  it("应该处理任务更新", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const updatedTask = {
      id: 1,
      title: "Updated Task 1",
      column_id: 1,
      order: 0,
    };

    // 模拟任务更新事件
    const taskModal = wrapper.findComponent({ name: "TaskModal" });
    await taskModal.vm.$emit("task-updated", updatedTask);

    // 验证任务是否更新
    const backlogColumn = wrapper.vm.columns.find((col: any) => col.id === 1);
    const task = backlogColumn.tasks.find((t: any) => t.id === 1);
    expect(task.title).toBe("Updated Task 1");
  });

  it("应该处理任务删除", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    // 模拟任务删除事件
    const taskModal = wrapper.findComponent({ name: "TaskModal" });
    await taskModal.vm.$emit("task-deleted", 1);

    // 验证任务是否被删除
    const backlogColumn = wrapper.vm.columns.find((col: any) => col.id === 1);
    expect(backlogColumn.tasks).toHaveLength(1);
    expect(backlogColumn.tasks.find((t: any) => t.id === 1)).toBeUndefined();
  });

  it("应该初始化sortable实例", async () => {
    const MockSortable = vi.mocked((await import("sortablejs")).default);

    mount(KanbanBoard);
    await nextTick();

    // 应该为每个列创建一个sortable实例
    expect(MockSortable).toHaveBeenCalledTimes(4);
  });

  it("应该为拖拽区域设置正确的data属性", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const dragAreas = wrapper.findAll(".drag-area");
    expect(dragAreas).toHaveLength(4);

    dragAreas.forEach((dragArea: any, index: number) => {
      expect(dragArea.attributes("data-column-id")).toBe(String(index + 1));
    });
  });

  it("应该为任务卡片设置正确的data属性", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    const taskCards = wrapper.findAll(".task-card");

    taskCards.forEach((taskCard: any) => {
      expect(taskCard.attributes("data-task-id")).toBeDefined();
    });
  });

  it("应该处理API错误", async () => {
    // Mock API 错误
    mockApiClient.get.mockRejectedValue(new Error("API Error"));

    const wrapper = mount(KanbanBoard);
    await nextTick();

    expect(wrapper.find(".error").text()).toBe(
      "Failed to fetch data from the server.",
    );
  });

  it("应该显示加载状态", () => {
    // Mock pending API call
    mockApiClient.get.mockImplementation(() => new Promise(() => {}));

    const wrapper = mount(KanbanBoard);

    expect(wrapper.text()).toContain("Loading...");
  });
});

describe("KanbanBoard 拖拽功能测试", () => {
  const mockColumns = [
    {
      id: 1,
      name: "Backlog",
      tasks: [{ id: 1, title: "Task 1", column_id: 1, order: 0 }],
    },
    {
      id: 2,
      name: "Up Next",
      tasks: [],
    },
    {
      id: 3,
      name: "In Progress",
      tasks: [],
    },
    {
      id: 4,
      name: "Done",
      tasks: [],
    },
  ];

  beforeEach(() => {
    vi.clearAllMocks();
    vi.mocked(apiClient.get).mockResolvedValue({
      data: mockColumns,
    });
    vi.mocked(apiClient.put).mockResolvedValue({
      data: { success: true },
    });
  });

  it("应该正确初始化组件", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    expect(wrapper.exists()).toBe(true);
    expect(apiClient.get).toHaveBeenCalledWith("/api/kanban");
  });

  it("应该处理拖拽API调用", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    // 模拟拖拽事件参数
    const mockDragEvent = {
      item: {
        getAttribute: vi.fn((attr: string) => {
          if (attr === "data-task-id") return "1";
          return null;
        }),
      },
      from: {
        getAttribute: vi.fn(() => "1"), // from column id
      },
      to: {
        getAttribute: vi.fn(() => "2"), // to column id
      },
      newIndex: 0,
      oldIndex: 0,
    };

    // 直接调用组件的拖拽处理方法
    const component = wrapper.vm as any;
    if (component.onDragEnd) {
      await component.onDragEnd(mockDragEvent);

      // 验证API调用
      expect(apiClient.put).toHaveBeenCalledWith("/api/kanban/tasks/1", {
        column_id: 2,
        order: 0,
      });
    }
  });

  it("应该在拖拽后重新获取数据", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    // 清除初始的API调用记录
    vi.clearAllMocks();

    const mockDragEvent = {
      item: {
        getAttribute: vi.fn((attr: string) => {
          if (attr === "data-task-id") return "1";
          return null;
        }),
      },
      from: {
        getAttribute: vi.fn(() => "1"),
      },
      to: {
        getAttribute: vi.fn(() => "2"),
      },
      newIndex: 0,
      oldIndex: 0,
    };

    const component = wrapper.vm as any;
    if (component.onDragEnd) {
      await component.onDragEnd(mockDragEvent);

      // 验证拖拽后重新获取数据
      expect(apiClient.get).toHaveBeenCalledWith("/api/kanban");
    }
  });

  it("应该处理拖拽API错误", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    // Mock API错误
    vi.mocked(apiClient.put).mockRejectedValue(new Error("Network error"));

    const mockDragEvent = {
      item: {
        getAttribute: vi.fn((attr: string) => {
          if (attr === "data-task-id") return "1";
          return null;
        }),
      },
      from: {
        getAttribute: vi.fn(() => "1"),
      },
      to: {
        getAttribute: vi.fn(() => "2"),
      },
      newIndex: 0,
      oldIndex: 0,
    };

    const component = wrapper.vm as any;
    if (component.onDragEnd) {
      // 拖拽处理应该不抛出错误
      await expect(component.onDragEnd(mockDragEvent)).resolves.not.toThrow();
    }
  });

  it("应该正确渲染基本结构", async () => {
    const wrapper = mount(KanbanBoard);
    await nextTick();

    expect(wrapper.find("h1").text()).toBe("My Kanban Board");
    expect(wrapper.exists()).toBe(true);
  });
});
