// @ts-check
const { test, expect } = require("@playwright/test");

test("Can authenticate", async ({ page }) => {
  await page.goto("/citizen_proposal/add");

  await page.getByRole("link", { name: "Authenticate with MitID" }).click();

  await page.getByLabel("Username", { exact: true }).fill("citizen1");

  await page.getByLabel("Password", { exact: true }).fill("citizen1");

  await page.getByRole("button", { name: "Login" }).click();

  await page.getByRole("link", { name: "Sign out" }).click();

  await expect(
    page.getByRole("link", { name: "Authenticate with MitID" }),
  ).toBeVisible();
});

test("Create proposal", async ({ page }) => {
  await page.goto("/citizen_proposal/add");

  await page.getByRole("link", { name: "Authenticate with MitID" }).click();

  await page.getByLabel("Username", { exact: true }).fill("citizen1");

  await page.getByLabel("Password", { exact: true }).fill("citizen1");

  await page.getByRole("button", { name: "Login" }).click();

  await expect(
    page.getByText("You're currently authenticated as Anders And"),
  ).toBeVisible();

  await expect(page.getByRole("link", { name: "Sign out" })).toBeVisible();

  await expect(page.getByLabel("Phone", { exact: true })).toHaveValue("");

  await page.getByLabel("Phone", { exact: true }).fill("12345678");

  await expect(page.getByLabel("Email", { exact: true })).toHaveValue("");

  await page.getByLabel("Email", { exact: true }).fill("borger87@eksemple.dk");

  await page
    .getByLabel("Title", { exact: true })
    .fill("Sådan løser vi alle problemer!");

  await page
    .getByLabel("Proposal", { exact: true })
    .fill("Jeg synes det er et stort problem at …");

  await page
    .getByLabel("Remarks", { exact: true })
    .fill("Dette vil også løse problemet med at …");

  await page.getByRole("button", { name: "Create proposal" }).click();

  expect(page).toHaveURL("/citizen_proposal/approve");

  await expect(
    page.getByRole("button", { name: "Approve proposal" }),
  ).toBeVisible();

  await expect(page.getByRole("link", { name: "Edit proposal" })).toBeVisible();

  await expect(
    page.getByRole("button", { name: "Cancel proposal" }),
  ).toBeVisible();

  await expect(page.getByText("borger87@eksemple.dk")).toBeVisible();

  await page.getByRole("link", { name: "Edit proposal" }).click();

  await expect(page.getByLabel("Email", { exact: true })).toHaveValue(
    "borger87@eksemple.dk",
  );

  await page.getByLabel("Email", { exact: true }).fill("borger87@eksempel.dk");

  await page.getByRole("button", { name: "Update proposal" }).click();

  await expect(page.getByText("borger87@eksempel.dk")).toBeVisible();

  await page.getByRole("button", { name: "Approve proposal" }).click();

  await expect(page.getByText("Thank you for your submission.")).toBeVisible();

  // Test that authentication is needed for creating another proposal.
  await page.goto("/citizen_proposal/add");
  await page.getByRole("link", { name: "Authenticate with MitID" }).click();
  await expect(page.getByLabel("Email", { exact: true })).toHaveValue("");
});

test("Cancel proposal", async ({ page }) => {
  await page.goto("/citizen_proposal/add");

  await page.getByRole("link", { name: "Authenticate with MitID" }).click();

  await page.getByLabel("Username", { exact: true }).fill("citizen1");

  await page.getByLabel("Password", { exact: true }).fill("citizen1");

  await page.getByRole("button", { name: "Login" }).click();

  await page.getByLabel("Phone", { exact: true }).fill("12345678");

  await page.getByLabel("Email", { exact: true }).fill("borger87@eksemple.dk");

  await page
    .getByLabel("Title", { exact: true })
    .fill("Sådan løser vi alle problemer!");

  await page
    .getByLabel("Proposal", { exact: true })
    .fill("Jeg synes det er et stort problem at …");

  await page
    .getByLabel("Remarks", { exact: true })
    .fill("Dette vil også løse problemet med at …");

  await page.getByRole("button", { name: "Create proposal" }).click();

  expect(page).toHaveURL("/citizen_proposal/approve");

  await page.getByRole("button", { name: "Cancel proposal" }).click();

  await expect(
    page.getByText("Your submission has been cancelled."),
  ).toBeVisible();
});
