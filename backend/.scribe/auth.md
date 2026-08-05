# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtén un token Bearer autenticándote en <code>POST /api/v1/auth/login</code> o registrando un usuario en <code>POST /api/v1/auth/register</code>.
