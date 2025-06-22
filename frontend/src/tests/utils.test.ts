import { describe, it, expect } from "vitest";

// Simple utility functions for testing
const formatDate = (date: Date): string => {
  return date.toISOString().split("T")[0];
};

const validateEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

describe("Utility Functions", () => {
  it("should format date correctly", () => {
    const date = new Date("2023-12-25T10:30:00Z");
    const formatted = formatDate(date);
    expect(formatted).toBe("2023-12-25");
  });

  it("should validate email correctly", () => {
    expect(validateEmail("test@example.com")).toBe(true);
    expect(validateEmail("invalid-email")).toBe(false);
    expect(validateEmail("test@")).toBe(false);
    expect(validateEmail("@example.com")).toBe(false);
  });
});
