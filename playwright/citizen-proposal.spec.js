// @ts-check
const { test, expect } = require('@playwright/test')

test('Create proposal', async ({ page }) => {
  await page.goto('/citizen_proposal/add')

  await page.getByLabel('Email')
    .fill('borger87@eksemple.dk')

  await page.getByLabel('Overskrift')
    .fill('Sådan løser vi alle problemer!')

  await page.getByLabel('Proposal')
    .fill('Jeg synes det er et stort problem at …')

  await page.getByLabel('Remarks')
    .fill('Dette vil også løse problemet med at …')

  await page.getByRole('button', { name: 'Create proposal' })
    .click()

  expect(page).toHaveURL('/citizen_proposal/approve')

  await expect(page.getByRole('button', { name: 'Approve proposal' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Edit proposal' })).toBeVisible()
  await expect(page.getByRole('button', { name: 'Cancel proposal' })).toBeVisible()

  await expect(page.getByText('borger87@eksemple.dk')).toBeVisible()

  await page.getByRole('link', { name: 'Edit proposal' })
    .click()

  await expect(page.getByLabel('Email'))
    .toHaveValue('borger87@eksemple.dk')

  await page.getByLabel('Email')
    .fill('borger87@eksempel.dk')

  await page.getByRole('button', { name: 'Update proposal' })
    .click()

  await expect(page.getByText('borger87@eksempel.dk')).toBeVisible()

  await page.getByRole('button', { name: 'Approve proposal' })
    .click()

  await expect(page.getByText('Thank you for your submission.')).toBeVisible()
})

test('Cancel proposal', async ({ page }) => {
  await page.goto('/citizen_proposal/add')

  await page.getByLabel('Email')
    .fill('borger87@eksemple.dk')

  await page.getByLabel('Overskrift')
    .fill('Sådan løser vi alle problemer!')

  await page.getByLabel('Proposal')
    .fill('Jeg synes det er et stort problem at …')

  await page.getByLabel('Remarks')
    .fill('Dette vil også løse problemet med at …')

  await page.getByRole('button', { name: 'Create proposal' })
    .click()

  expect(page).toHaveURL('/citizen_proposal/approve')

  await page.getByRole('button', { name: 'Cancel proposal' })
    .click()

  await expect(page.getByText('Your submission has been cancelled.')).toBeVisible()
})
