import { test, expect } from '@playwright/test';

test.describe('Testy logowania', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:8000/login', { timeout: 200000 });
  });


  //test.afterEach(async ({ page }) => {
  //  await page.close();
  //});

  test('Sprawdzenie widocznosci przycisku rejestracji', async ({ page }) => {
    const singInButton = await page.getByRole('button', { name: 'Sign In' });
    await expect(singInButton).toBeVisible();
  });

  test('Sprawdzenie działania linku Register', async ({ page }) => {
    await page.getByRole('link', { name: 'Register' }).click();
    await expect(page).toHaveURL('http://localhost:8000/register', { timeout: 100000 });
  });


  // test z poprawnym loginem i hasłem zależny od wcześniejszych testów rejestracji
  // po nowym uruchomieniu aplikacji testowej użytkownika nie mam w bazie danych (jest chyba tylko lokalnie)
  // test ten nie przejdzie
  test('Logowanie z poprawnymi danymi', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('Testowy@testowy.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('Testowy1!');
    const singInButton = page.getByRole('button', { name: 'Sign In' });
    await singInButton.click();
    await page.waitForURL('http://localhost:8000/dashboard', { timeout: 200000 });
    await expect(page).toHaveURL('http://localhost:8000/dashboard', { timeout: 200000 });
  });


  test('Logowanie z pustymi danymi', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('');
    await page.getByRole('textbox', { name: 'Password' }).fill('');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=All fields are required')).toHaveText('All fields are required');

    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z niepoprawnymi danymi', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('test@TEST.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('Password1!');
    await page.getByRole('button', { name: 'SIGN IN' }).click();

    await expect(page.locator('text=Login failed. Please try')).toHaveText('Login failed. Please try again.');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z poprawnym loginem, błędnym hasłem', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('Testowy@testowy.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('Passwd');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=Login failed. Please try')).toHaveText('Login failed. Please try again.');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z poprawnym loginem, pustym hasłem', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('Testowy@testowy.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=All fields are required')).toHaveText('All fields are required');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z pustym loginem, poprawnym hasłem', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('');
    await page.getByRole('textbox', { name: 'Password' }).fill('Testowy1!');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=All fields are required')).toHaveText('All fields are required');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z niepoprawnym loginem i poprawnym hasłem', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('invaliduser@example.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('Password1!');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=Login failed. Please try')).toHaveText('Login failed. Please try again.');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z SQL Injection w polu login', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill("admin' OR '1'='1");
    await page.getByRole('textbox', { name: 'Password' }).fill('SomePassword!');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=Incorrect email or phone')).toHaveText('Incorrect email or phone number');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z SQL Injection w polu hasło', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('testowy@example.com');
    await page.getByRole('textbox', { name: 'Password' }).fill("password' OR '1'='1");
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=Login failed. Please try')).toHaveText('Login failed. Please try again.');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });

  // nie wiem czy czasem nie nalezy ograniczyć ilości znaków w polach login i hasło
  test('Logowanie z długim loginem i hasłem', async ({ page }) => {
    const longString = 'a'.repeat(50);
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill(longString + '@testowy.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('A' + longString + '1!');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await page.waitForURL('http://localhost:8000/dashboard', { timeout: 200000 });
    await expect(page).toHaveURL('http://localhost:8000/dashboard', { timeout: 200000 });
  });

  // traktowane jako niewłaściwe dane
  test('Logowanie ze znakami specjalnymi', async ({ page }) => {
    const specialLogin = '!#$%^&*()@testowy_+={}[]:";\'<>?,./.com';
    const specialPassword = 'Aa1!@#$%^&*()_+={}[]:";\'<>?,./';
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill(specialLogin);
    await page.getByRole('textbox', { name: 'Password' }).fill(specialPassword);
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.locator('text=Incorrect email or phone')).toHaveText('Incorrect email or phone number');
    await expect(page).toHaveURL('http://localhost:8000/login', { timeout: 200000 });
  });


  test('Logowanie z różnymi wielkościami liter', async ({ page }) => {
    await page.getByRole('textbox', { name: 'Email or Phone' }).fill('Testowy@testowy.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('Testowy1!');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await page.waitForURL('http://localhost:8000/dashboard', { timeout: 200000 });
    await expect(page).toHaveURL('http://localhost:8000/dashboard', { timeout: 200000 });
  });
});