# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_BEARER_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Authenticate using a Sanctum personal access token. Pass the token in the <code>Authorization</code> header as <code>Bearer {token}</code>. You can obtain a token by logging in via the web application.
