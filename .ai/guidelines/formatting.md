# Code Formatting

- All files are indented with tabs, never spaces (see `.editorconfig`). Exceptions: YAML and Markdown.
- PHP is formatted with PHP-CS-Fixer (`.php-cs-fixer.dist.php`), not Pint. Pint cannot indent with tabs, so do not install or run it.
- After changing PHP files, run `composer format` (fix) or `composer format:check` (dry run).
- Blade, JS and CSS are not covered by the fixer; indent them with tabs by hand.
- Built assets in `public/build` are committed: run `npm run build` before committing frontend changes.
