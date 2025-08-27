# API Versioning Documentation

## Overview

The Translation Management Service API now supports versioning to ensure backward compatibility while allowing for future enhancements.

## Version Structure

### Current Version: v1

All current API endpoints are available under version 1 (`v1`).

### URL Patterns

- **Versioned URLs**: `/api/v1/{endpoint}`
- **Default URLs**: `/api/{endpoint}` (backward compatibility)

Both patterns point to the same v1 implementation.

## Available Endpoints

### Authentication
- `POST /api/v1/login` - User authentication
- `POST /api/v1/logout` - User logout
- `GET /api/v1/user` - Get current user

### Translation Management
- `GET /api/v1/translations` - List/search translations
- `POST /api/v1/translations` - Create new translation
- `GET /api/v1/translations/{id}` - Get specific translation
- `PUT /api/v1/translations/{id}` - Update translation
- `DELETE /api/v1/translations/{id}` - Delete translation

### Export Functionality
- `GET /api/v1/export/{locale}` - Export translations for locale
- `GET /api/v1/export/{locale}?tags=web,auth` - Export with tag filtering
- `GET /api/v1/export/{locale}?stream=true` - Stream large exports

### User Management
- `GET /api/v1/users` - List users
- `GET /api/v1/users/{id}` - Get specific user

## Response Format

All v1 API responses include version information:

```json
{
    "success": true,
    "message": "Success",
    "data": {...},
    "version": "1.0.0"
}
```

## Error Responses

```json
{
    "success": false,
    "message": "Error message",
    "version": "1.0.0",
    "errors": {...}
}
```

## Migration Guide

### From Legacy to v1

If you're currently using the legacy endpoints, you can:

1. **Keep using current URLs** - They will continue to work
2. **Migrate to v1 URLs** - Update to `/api/v1/` prefix for future-proofing

### Example Migration

```bash
# Legacy (still works)
GET /api/export/en

# v1 (recommended)
GET /api/v1/export/en
```

## Future Versions

When v2 is introduced:

- v1 endpoints will remain stable
- v2 will be available at `/api/v2/`
- Breaking changes will only occur in new versions
- Migration guides will be provided for each version

## Performance

- All v1 endpoints maintain the same performance optimizations
- Export endpoints target <500ms response times
- Streaming available for large datasets
