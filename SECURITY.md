# Security Policy

Security fixes are provided for the latest released major version.

Report suspected vulnerabilities privately to `brian.schaeffner@sympress.de`.
Include affected versions, impact, reproduction steps and logs with credentials
removed. Do not include passwords, connection URLs or personal production data
in issues or test fixtures.

Keep Doctrine connection credentials outside source control, use parameterized
queries and review database grants and migration scope in the consuming
application. Database ownership filters do not replace access control. Doctrine
does not automatically authorize queries or isolate tenants.
