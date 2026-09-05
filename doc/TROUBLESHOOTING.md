# Why won't errors show up?

```php
cat > /tmp/catch.php <<'EOF'
<?php
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
        fwrite(STDERR, sprintf("\n[FATAL] %s\n  %s:%d\n", $e['message'], $e['file'], $e['line']));
    }
});
EOF

php -d auto_prepend_file=/tmp/catch.php index.php
```
