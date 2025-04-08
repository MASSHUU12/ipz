import { test, expect } from "@playwright/test";

test("has title", async ({ page }) => {
  await page.goto("http://0.0.0.0:8000");

  // Expect a title "to contain" a substring.
  // It's the title from .env file APP_NAME.
  await expect(page).toHaveTitle(/- IPZ/);
});
