# Authentication API Documentation

This document outlines the authentication endpoints for user registration, login, and logout.

## 1. User Registration

### Endpoint

```
POST /api/register
```

### Headers

```
Accept application/json
```

### Request Parameters

| Parameter               | Type   | Required                    | Description                   |
|-------------------------|--------|-----------------------------|-------------------------------|
| `email`                 | string | Required if no phone_number | User's email address          |
| `phone_number`          | string | Required if no email        | User's phone number           |
| `password`              | string | Yes                         | User's password               |
| `password_confirmation` | string | Yes                         | Must match the password field |

**Note:** Either `email` or `phone_number` must be provided, but both are not required.

### Validation Rules

- **Email**:
    - Required if phone_number is not provided
    - Must be unique in the system
    - Must be a valid email format

- **Phone Number**:
    - Required if email is not provided
    - Must be unique in the system
    - Must be a valid international phone number format

- **Password**:
    - Required
    - Minimum length: 8 characters
    - Maximum length: 255 characters
    - Must contain at least one lowercase letter
    - Must contain at least one uppercase letter
    - Must contain at least one number
    - Must contain at least one special character (@, $, !, %, *, ?, &)
    - Must be confirmed with `password_confirmation` field

### Success Response

**Code**: `201 Created`

**Content Example**:

```json
{
  "user": {
    "email": "example@example.com",
    "phone_number": null,
    "updated_at": "2025-03-10T11:43:23.000000Z",
    "created_at": "2025-03-10T11:43:23.000000Z",
    "id": 1
  },
  "token": "1|Xisr9laFEoIDeMY8Pc7TcdpoJNsoJg3h0nkRHRet14e31ce9"
}
```

The response includes:

- `user`: Object containing the newly created user details
- `token`: Authentication token that should be used for subsequent authenticated requests

### Error Responses

**Code**: `422 Unprocessable Entity`

**Content Example** (when email is already in use):

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

**Content Example** (when password confirmation doesn't match):

```json
{
  "message": "The password field confirmation does not match.",
  "errors": {
    "password": [
      "The password field confirmation does not match."
    ]
  }
}
```

**Content Example** (when password requirements aren't met):

```json
{
  "message": "The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.",
  "errors": {
    "password": [
      "The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character."
    ]
  }
}
```

**Code**: `500 Internal Server Error`

**Content Example** (when user registration fails):

```json
{
  "message": "There was an error during user registration. Please try again."
}
```

## 2. User Login

### Endpoint

```
POST /api/login
```

### Headers

```
Accept application/json
```

### Request Parameters

| Parameter      | Type   | Required                    | Description          |
|----------------|--------|-----------------------------|----------------------|
| `email`        | string | Required if no phone_number | User's email address |
| `phone_number` | string | Required if no email        | User's phone number  |
| `password`     | string | Yes                         | User's password      |

**Note:** Either `email` or `phone_number` must be provided, but both are not required.

### Validation Rules

- **Email**:
    - Required if phone_number is not provided
    - Must be a valid email format

- **Phone Number**:
    - Required if email is not provided
    - Must be a valid international phone number format

- **Password**:
    - Required

### Account Security

The system implements account security features:

- Failed login attempts are tracked
- After 5 failed login attempts, the account will be temporarily blocked for 4 hours
- Failed login counter is reset after a successful login
- When an account is blocked, login attempts and any protected routes will be rejected until the block expires

### Success Response

**Code**: `201 Created`

**Content Example**:

```json
{
  "user": {
    "id": 1,
    "email": "example@example.com",
    "phone_number": null,
    "created_at": "2025-03-10T11:43:23.000000Z",
    "updated_at": "2025-03-10T11:43:23.000000Z"
  },
  "token": "2|LKasd82jd92jd02jdKJkajsd92jdK2j3d0s3dk"
}
```

### Error Response

**Code**: `401 Unauthorized`

**Content Example** (incorrect credentials):

```json
{
  "message": "The provided credentials are incorrect."
}
```

**Content Example** (blocked account):

```json
{
  "message": "Your account is temporarily blocked. Please try again later."
}
```

## 3. User Logout

### Endpoint

```
POST /api/logout
```

### Headers

```
Accept application/json
Authorization: Bearer {token}
```

### Authentication

This endpoint requires authentication. The token received during login or registration must be included in the request
header.

```
Authorization: Bearer {token}
```

### Request Parameters

No parameters required.

### Success Response

**Code**: `200 OK`

**Content Example**:

```json
{
  "message": "Tokens Revoked"
}
```

### Error Response

**Code**: `401 Unauthorized`

**Content Example**:

```json
{
  "message": "Unauthenticated."
}
```

## Notes

- After successful login or registration, the returned token should be included in the `Authorization` header of
  subsequent requests using the format `Bearer {token}`.
- The logout endpoint will revoke all tokens for the authenticated user.
- Authentication is handled using Laravel Sanctum.
