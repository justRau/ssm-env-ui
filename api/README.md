# SSM Parameter Editor - PHP Backend

This is the PHP backend for the SSM Parameter Editor, converted from the original Go CLI tool.

## Requirements

- PHP 8.3 or higher
- Composer
- AWS credentials configured

## Setup

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure AWS credentials** (same as the Go tool):
   - Environment variables: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`
   - Or use `~/.aws/credentials` file
   - Or use IAM roles if running on AWS

3. **Start the development server:**
   ```bash
   cd api
   php -S localhost:8080
   ```

4. **Test the setup:**
   ```bash
   curl "http://ssm-env.test/api/test.php"
   ```

## API Endpoints

### GET /test.php
Health check endpoint to verify the backend is working.

**Example:**
```bash
curl "http://ssm-env.test/api/test.php"
```

### GET /getParameters.php
Fetch parameters by prefix.

**Query Parameters:**
- `prefix` (required): SSM parameter prefix

**Example:**
```bash
curl "http://ssm-env.test/api/getParameters.php?prefix=/myapp/prod/"
```

### POST /createParameter.php
Create a new parameter.

**Request Body:**
```json
{
  "prefix": "/myapp/prod/",
  "name": "database_url",
  "value": "mysql://localhost:3306/mydb",
  "type": "String"
}
```

**Example:**
```bash
curl -X POST http://ssm-env.test/api/createParameter.php \
  -H "Content-Type: application/json" \
  -d '{"prefix":"/myapp/prod/","name":"test","value":"hello","type":"String"}'
```

### PUT /updateParameter.php
Update an existing parameter.

**Request Body:**
```json
{
  "fullName": "/myapp/prod/database_url",
  "value": "mysql://localhost:3306/newdb",
  "type": "String"
}
```

**Example:**
```bash
curl -X PUT http://ssm-env.test/api/updateParameter.php \
  -H "Content-Type: application/json" \
  -d '{"fullName":"/myapp/prod/test","value":"updated","type":"String"}'
```

## Parameter Types

- `String`: Plain text value
- `StringList`: Comma-separated list of values
- `SecureString`: Encrypted value using AWS KMS

## Error Handling

All endpoints return JSON responses with error messages:

```json
{
  "error": "Error message here"
}
```

HTTP status codes:
- `200`: Success
- `400`: Bad request (missing fields, invalid JSON)
- `405`: Method not allowed
- `409`: Conflict (parameter already exists)
- `500`: Server error (AWS errors, etc.)