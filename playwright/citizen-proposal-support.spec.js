// @ts-check
const { test, expect } = require("@playwright/test");

test("Support proposal", async ({ page }) => {
  await page.goto("/borgerforslag");

  await page.getByText("Borgerforslag nummer 1").click();

  await page
    .getByRole("link", { name: "Support the proposal" })
    .first()
    .click();

  await page.getByRole("link", { name: "Authenticate with MitID" }).click();

  await page.getByLabel("Username", { exact: true }).fill("citizen1");

  await page.getByLabel("Password", { exact: true }).fill("citizen1");

  await page.getByRole("button", { name: "Login" }).click();

  await expect(
    page.getByText("You're currently authenticated as Anders And"),
  ).toBeVisible();

  await expect(page.getByRole("link", { name: "Sign out" })).toBeVisible();

  await expect(page.getByLabel("Name", { exact: true })).toHaveValue(
    "Anders And",
  );

  await page.getByRole("button", { name: "Support proposal" }).click();

  await expect(page.getByText("Thank you for your support.")).toBeVisible();

  // Test that must re-authenticate to support a proposal
  await page
    .getByRole("link", { name: "Support the proposal" })
    .first()
    .click();

  await page.getByRole("link", { name: "Authenticate with MitID" }).click();

  // @todo User is not signed out from IdP
  // await page.getByLabel("Username", { exact: true }).fill("citizen1");
  // await page.getByLabel("Password", { exact: true }).fill("citizen1");
  // await page.getByRole("button", { name: "Login" }).click();

  // Test that citizen cannot support proposal more than once
  await expect(
    page.getByText("You already supported this proposal"),
  ).toBeVisible();

  // Test that citizen must re-authenticate to support another proposal
  // await page.goto("/borgerforslag");

  // await page.getByText("Borgerforslag nummer 2").click();

  // await page.getByRole("link", { name: "Support the proposal" }).first().click();

  // await page.getByRole("link", { name: "Authenticate with MitID" }).click();

  // @todo User is not signed out from IdP
  // await page.getByLabel("Username", { exact: true }).fill("citizen1");
  // await page.getByLabel("Password", { exact: true }).fill("citizen1");
  // await page.getByRole("button", { name: "Login" }).click();
});
