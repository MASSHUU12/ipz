import {test, expect} from '@playwright/test';

test.describe('Testy strony rejestracji', () => {

    test.beforeEach(async ({page}) => {
        await page.goto('http://localhost:8000/register');
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
        const uniqueEmail = `randomRegist${Date.now()}@testowy.com`;
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill(uniqueEmail);
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('Testowy1!');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('Testowy1!');

        const registerButton = page.getByRole('button', { name: 'REGISTER' });
        await registerButton.click();
        await page.getByRole('alert',{name:'Check your mailbox We have sent you a verification email.'}).isVisible();

        await expect(page).toHaveURL('http://localhost:8000/login?verification=1');
    });


    test('Rejestracja z pustymi danymi', async ({page}) => {
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill('');
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('');
        
        const registerButton = page.getByRole('button', { name: 'REGISTER' });
        await registerButton.click();
        await expect(page.locator('text=All fields are required')).toHaveText('All fields are required');


        await expect(page).toHaveURL('http://localhost:8000/register');
    });

   
    test('Rejestracja z istniejacym loginem', async ({page}) => {
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill('testRegist@testowy.com');
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('123Haslo!');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('123Haslo!');

        const registerButton = await page.getByRole('button', { name: 'Register' });
        await registerButton.click();

        await expect(page.locator('text=The email has already been taken.')).toHaveText('The email has already been taken.');
        await expect(page).toHaveURL('http://localhost:8000/register');
    });


    test('Rejestracja z błędnym potwierdzeniem hasła', async ({page}) => {
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill('Testowy2@testowy.com');
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('CorrectPassword1!');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('WrongPassword1!');

        const registerButton = await page.getByRole('button', { name: 'Register' });
        await registerButton.click();

        await expect(page.locator('text=Passwords are not the same')).toHaveText('Passwords are not the same');
        await expect(page).toHaveURL('http://localhost:8000/register');
    });

    // format hasła 8 znaków wielka litera, mała litera, cyfra, znak specjalny
    // dane użytkownika do poprawy.
    test('Rejestracja z niepoprawnym formatem hasła', async ({page}) => {
        await page.getByRole('textbox', { name: 'Email or Phone' }).fill('Testowy2@testowy.com');
        await page.getByRole('textbox', { name: 'Password', exact:true }).fill('passwd');
        await page.getByRole('textbox', { name: 'Confirm Password' }).fill('passwd');

        const registerButton = await page.getByRole('button', { name: 'Register' });
        await registerButton.click();

        await expect(page.locator('text=Password must have at least 8 letters')).toHaveText('Password must have at least 8 letters');
        await expect(page).toHaveURL('http://localhost:8000/register');
    });
});