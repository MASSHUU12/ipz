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
    - Maximum length: 20 characters
    - *Note: Additional phone number validation will be implemented in the future*

- **Password**:
    - Required
    - Minimum length: 8 characters
    - Maximum length: 255 characters
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
    - Maximum length: 20 characters

- **Password**:
    - Required

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

**Content Example**:

```json
{
  "message": "The provided credentials are incorrect."
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