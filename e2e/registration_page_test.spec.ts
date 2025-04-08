import {test, expect} from '@playwright/test';

test.describe('Testy strony rejestracji', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('localhost:8000/register');
    });

    test.afterEach(async ({page}) => {
        await page.close();
    });

    test('Sprawdzenie tytulu strony', async ({page}) => {
        const title = await page.title();
        expect(title).toBe('- IPZ');
    });

    test('Sprawdzenie widoczności formularza rejestracji', async ({page}) => {
        const form = await page.locator('#app');
        await expect(form).toBeVisible();
    });

    test('Sprawdzenie widoczności przycisku rejestracji', async ({page}) => {
        const registerButton = await page.getByRole('button', { name: 'Register' });
        await expect(registerButton).toBeVisible();
    });

    test('sprawdzenie działania linku Sing in', async ({page}) => {
        await page.getByRole('link', { name: 'Sign in' }).click();
        await expect(page).toHaveURL('http://localhost:8000/login');
    
    });

    test('Rejestracja z poprawnymi danymi', async ({page}) => {
        await page.getByRole('textbox', { name: 'Name' }).fill('Jan Kowalski');
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill('test@example.com');
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('Password1!');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('Password1!');

        const registerButton = page.getByRole('button', { name: 'REGISTER' });
        await registerButton.click();

        await expect(page).toHaveURL('http://localhost:8000/profile');

        await expect(page.locator('.error-message')).toHaveCount(0);
    });


    test('Rejestracja z pustymi danymi', async ({page}) => {
        await page.getByRole('textbox', { name: 'Name' }).fill('');
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill('');
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('');
        
        const registerButton = page.getByRole('button', { name: 'REGISTER' });
        await registerButton.click();
        
        const errorMessage = await page.locator('.error-message').textContent();
        expect(errorMessage).toContain('required fields');

        await expect(page).toHaveURL('http://localhost:8000/register');
    });

    // przykładowe dane do zmiany, baza jeszcze jest pusta
    test('Rejestracja z istniejacym loginem', async ({page}) => {
        await page.getByPlaceholder('Name').fill('existingUser');
        await page.getByPlaceholder('Email or Phone').fill('existing@test.pl');
        await page.getByPlaceholder('Password').fill('123Haslo!');
        await page.getByPlaceholder('Confirm Password').fill('123Haslo!');

        const registerButton = await page.getByRole('button', { name: 'Register' });
        await registerButton.click();

        await expect(page.locator('.error-message')).toHaveText('user already exists');
        await expect(page).toHaveURL('http://localhost:8000/register');

        await expect(registerButton).toBeDisabled();
    });


    // dane użytkownika do poprawy.
    test('Rejestracja z błędnym potwierdzeniem hasła', async ({page}) => {
        await page.getByPlaceholder('Name').fill('CorrectName');
        await page.getByPlaceholder('Email or Phone').fill('correctEmailOrPhone');
        await page.getByPlaceholder('Password').fill('CorrectPassword1!');
        await page.getByPlaceholder('Confirm Password').fill('WrongPassword1!');

        const registerButton = await page.getByRole('button', { name: 'Register' });
        await registerButton.click();

        await expect(page.locator('.error-message')).toHaveText('password does not match');
    });

    // format hasła 8 znaków wielka litera, mała litera, cyfra, znak specjalny
    // dane użytkownika do poprawy.
    test('Rejestracja z niepoprawnym formatem hasła', async ({page}) => {
        await page.getByPlaceholder('Name').fill('CorrectName');
        await page.getByPlaceholder('Email or Phone').fill('correctEmailOrPhone');
        await page.getByPlaceholder('Password').fill('psw');
        await page.getByPlaceholder('Confirm Password').fill('pws');

        const registerButton = await page.getByRole('button', { name: 'Register' });
        await registerButton.click();

        await expect(page.locator('.error-message')).toContainText([
            'małe i duże litery',
            'przynajmniej jedna cyfra',
            'przynajmniej jeden znak specjalny'
          ]);
    });
});