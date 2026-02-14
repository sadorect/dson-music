#!/bin/bash
git add .
git commit -m "Phase 1: Critical stability fixes

- Remove blocking dd() from EnsureArtistProfileComplete middleware
- Fix admin routes compilation errors (removed invalid namespace, fixed middleware)
- Fix AdminMiddleware authorization logic (isAdmin as method, proper redirect)
- Create admin reports dashboard view
- Add super admin gate for admin user management

All critical stability issues resolved. Application now stable for internal testing."
