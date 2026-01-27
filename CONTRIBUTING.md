# Contributing to DSON Music

Thank you for considering contributing to DSON Music! We welcome contributions from the community.

## Code of Conduct

Please be respectful and constructive in all interactions. We aim to maintain a welcoming and inclusive environment for all contributors.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- A clear and descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Screenshots (if applicable)
- Your environment (OS, PHP version, browser, etc.)

### Suggesting Enhancements

We welcome feature suggestions! Please create an issue with:
- A clear and descriptive title
- Detailed description of the proposed feature
- Rationale for why this feature would be useful
- Any examples or mockups (if applicable)

### Pull Requests

1. **Fork the repository** and create your branch from `main`:
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. **Make your changes**:
   - Write clear, concise commit messages
   - Follow the existing code style
   - Add tests for new features
   - Update documentation as needed

3. **Test your changes**:
   ```bash
   # Run tests
   php artisan test
   
   # Check code style
   ./vendor/bin/pint
   
   # Run security audit
   npm audit
   ```

4. **Push to your fork** and submit a pull request:
   ```bash
   git push origin feature/my-new-feature
   ```

5. **PR Requirements**:
   - All tests must pass
   - Code must follow Laravel coding standards (use Pint)
   - No security vulnerabilities
   - Include description of changes
   - Link related issues

## Development Setup

1. Clone your fork:
   ```bash
   git clone https://github.com/your-username/dson-music.git
   cd dson-music
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Create database and migrate:
   ```bash
   php artisan migrate --seed
   ```

5. Build assets:
   ```bash
   npm run dev
   ```

## Coding Standards

### PHP/Laravel

- Follow [Laravel coding standards](https://laravel.com/docs/contributions#coding-style)
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Use type hints for parameters and return types
- Write PHPDoc comments for complex methods
- Keep methods focused and single-purpose

### JavaScript

- Use ES6+ syntax
- Follow existing patterns in the codebase
- Keep functions small and focused
- Add comments for complex logic

### Blade Templates

- Use Blade components when possible
- Keep templates clean and readable
- Extract reusable sections into components
- Use proper indentation

### Database

- Always create migrations for schema changes
- Use meaningful column and table names
- Add indexes for frequently queried columns
- Write seeders for test data

### Testing

- Write tests for new features
- Maintain or improve test coverage
- Use descriptive test method names
- Follow AAA pattern (Arrange, Act, Assert)

## Commit Messages

Write clear commit messages following this format:

```
feat: add user profile customization

- Add profile picture upload
- Add bio field
- Add social links section
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

## Branch Naming

Use descriptive branch names:

- `feature/user-notifications`
- `fix/login-validation`
- `docs/api-documentation`
- `refactor/cache-service`

## Review Process

1. Pull requests require at least one approval
2. All CI checks must pass
3. Code must be up to date with main branch
4. Conflicts must be resolved

## Getting Help

- Check existing issues and documentation
- Join our community discussions
- Tag maintainers in your PR if stuck

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing to DSON Music! 🎵
