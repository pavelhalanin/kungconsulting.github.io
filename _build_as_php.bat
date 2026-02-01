CALL jekyll clean
CALL jekyll build
cd _cron
cd rename-index-html-to-index-php
CALL php main.php
cd ..
cd ..
