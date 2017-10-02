# How to Contribute

## Pull Requests

1. Create your own [fork](https://help.github.com/articles/fork-a-repo) of this repo
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the **develop** branch

It is very important to separate new features or improvements into separate
feature branches, and to send a pull request for each branch. This allows me to
review and pull in new features or improvements individually.

## Code Standards

A [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) config
file has been included in the project. Please run `php-cs-fixer` and commit any changes
before submitting your pull request.

```bash
php-cs-fixer fix --config .php_cs --verbose
```
## Unit Testing

All pull requests must be accompanied by passing PHPUnit unit tests and
complete code coverage.

[Learn about PHPUnit](https://github.com/sebastianbergmann/phpunit/)
