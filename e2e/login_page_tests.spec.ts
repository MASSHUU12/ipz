import { test, expect } from '@playwright/test';

test.describe('Testy logowania', () => {
  
  // Przed każdym testem otwieramy stronę logowania.
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8000/login');
  });

  // Po każdym teście zamykamy stronę (uwaga: Playwright domyślnie dba o czystość kontekstu)
  test.afterEach(async ({ page }) => {
    await page.close();
  });

  test('Sprawdzenie widocznosci przycisku rejestracji', async ({ page }) => {
    const registerButton = await page.getByRole('button', { name: 'Sign In' });
    await expect(registerButton).toBeVisible();
  });

  test('Sprawdzenie działania linku Register', async ({ page }) => {
    await page.getByRole('link', { name: 'Register' }).click();
    await expect(page).toHaveURL('http://localhost:8000/register');
  });

  test('Logowanie z poprawnymi danymi', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('testowy@example.com');
    await page.getByPlaceholder('Password').fill('Password1!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page).toHaveURL('http://localhost:8000/dashboard');
  });


  test('Logowanie z pustymi danymi', async ({ page }) => {
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("The 'login'/'password' field cannot be empty");

    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z niepoprawnymi danymi', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('nonexistent@example.com');
    await page.getByPlaceholder('Password').fill('WrongPassword1!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("Incorrect 'login' or 'password'");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z poprawnym loginem, błędnym hasłem', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('testowy@example.com');
    await page.getByPlaceholder('Password').fill('WrongPassword!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("Incorrect 'login' or 'password'");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z poprawnym loginem, pustym hasłem', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('testowy@example.com');
    await page.getByPlaceholder('Password').fill('');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("The 'login'/'password' field cannot be empty");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z pustym loginem, poprawnym hasłem', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('');
    await page.getByPlaceholder('Password').fill('Password1!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("The 'login'/'password' field cannot be empty");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z niepoprawnym loginem i poprawnym hasłem', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('invaliduser@example.com');
    await page.getByPlaceholder('Password').fill('Password1!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("Incorrect 'login' or 'password'");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });

  test('Logowanie z SQL Injection w polu login', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill("admin' OR '1'='1");
    await page.getByPlaceholder('Password').fill('SomePassword!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("Unauthorized Access");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z SQL Injection w polu hasło', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('testowy@example.com');
    await page.getByPlaceholder('Password').fill("password' OR '1'='1");
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText("Unauthorized access");
    await expect(page).toHaveURL('http://localhost:8000/login');
  });


  test('Logowanie z długim loginem i hasłem', async ({ page }) => {
    const longString = 'a'.repeat(1000);
    await page.getByPlaceholder('Email or Phone').fill(longString);
    await page.getByPlaceholder('Password').fill(longString);
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('.error-message')).toHaveText(/Login\/password is too long/);
    await expect(page).toHaveURL('http://localhost:8000/login');
  });

  test('Logowanie ze znakami specjalnymi', async ({ page }) => {
    const specialLogin = '!@#$%^&*()_+={}[]:";\'<>?,./';
    const specialPassword = '!@#$%^&*()_+={}[]:";\'<>?,./';
    await page.getByPlaceholder('Email or Phone').fill(specialLogin);
    await page.getByPlaceholder('Password').fill(specialPassword);
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page).toHaveURL('http://localhost:8000/dashboard');
  });


  test('Logowanie z różnymi wielkościami liter', async ({ page }) => {
    await page.getByPlaceholder('Email or Phone').fill('AdMiN@example.com');
    await page.getByPlaceholder('Password').fill('PaSSworD1!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page).toHaveURL('http://localhost:8000/dashboard');
  });

});