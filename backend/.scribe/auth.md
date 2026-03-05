# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Obtenha seu token via **POST /api/v1/auth/login** com `email` e `password`. Use o valor retornado no header: `Authorization: Bearer {TOKEN}`.
