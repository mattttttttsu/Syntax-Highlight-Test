setlocal
phpunit --coverage-html coverage --log-xml log/test_result.log --testdox-html log/testdox.html All.php
endlocal