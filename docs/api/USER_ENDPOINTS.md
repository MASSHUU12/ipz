# User API Documentation

This document outlines the endpoints for managing the current authenticated user's account.

## 1. Get Current User

Retrieves the currently authenticated user's information.

### Endpoint

```
GET /api/user
```

### Headers

```
Accept: application/json
Authorization: Bearer {token}
```

### Success Response

**Code**: `200 OK`

**Content Example**:

```json
{
  "user": {
    "id": 1,
    "email": "user@example.com",
    "phone_number": "+1234567890",
    "created_at": "2025-03-10T11:43:23.000000Z",
    "updated_at": "2025-03-19T09:22:15.000000Z"
  }
}
```

### Error Responses

**Code**: `401 Unauthorized`

**Content Example**:

```json
{
  "message": "The provided credentials are incorrect."
}
```

## 2. Update Current User

Updates the currently authenticated user's information.

### Endpoint

```
PATCH /api/user
```

### Headers

```
Accept: application/json
Authorization: Bearer {token}
```

### Request Parameters

| Parameter      | Type   | Required | Description          |
| `email`        | string | No       | User's email address |
| `phone_number` | string | No       | User's phone number  |
| `password`     | string | No       | User's new password  |

### Validation Rules

Validation Rules

- **Email**:
    - Must be a valid email format
    - Must be unique in the system

- **Phone Number**:
    - Must be unique in the system
    - Maximum length: 20 characters
    - Must be a valid international phone number format

- **Password**:
    - Minimum length: 8 characters
    - Maximum length: 255 characters
    - Must contain at least one lowercase letter
    - Must contain at least one uppercase letter
    - Must contain at least one number
    - Must contain at least one special character (@$!%*?&)

### Account Security

The system implements account security features:

- Failed login attempts are tracked
- After 5 failed login attempts, the account will be temporarily blocked for 4 hours
- Failed login counter is reset after a successful login
- When an account is blocked, login attempts and any protected routes will be rejected until the block expires

### Success Response

**Code**: `200 OK`

**Content Example**:

```json
{
  "message": "User updated successfully",
  "user": {
    "id": 2,
    "email": "example@example.com",
    "phone_number": "123456788",
    "email_verified_at": null,
    "phone_number_verified_at": null,
    "created_at": "2025-03-19T11:29:03.000000Z",
    "updated_at": "2025-03-19T11:33:35.000000Z"
  }
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

**Content Example** (when password doesn't meet requirements):

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

## 3. Delete Current User

Permanently deletes the currently authenticated user's account.

### Endpoint

```
DELETE /api/user
```

### Headers

```
Accept: application/json
Authorization: Bearer {token}
```

### Success Response

**Code**: `200 OK`

**Content Example**:

```json
{
  "message": "User deleted successfully"
}
```

### Error Responses

**Code**: `401 Unauthorized`

**Content Example**:

```json
{
  "message": "Unauthenticated."
}
```

**Code**: `500 Internal Server Error`

**Content Example**:

```json
{
  "message": "There was an error during user deletion"
}
```
