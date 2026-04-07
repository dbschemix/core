This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.
## Commands
All `make` targets run inside Docker. To run tools directly (e.g., in this environment where vendor/ is already present):

```bash
# Tests

./vendor/bin/phpunit --configuration phpunit.xml.dist                                                                                                                                   
./vendor/bin/phpunit --configuration phpunit.xml.dist tests/FilesUpTest.php  # Run a single test file                      

./vendor/bin/phpcs # code style check  
                                                                                                      
./vendor/bin/psalm --show-info --no-diff --no-cache # Static analysis
./vendor/bin/phpstan analyze -c phpstan.neon
                         
# Auto-fix
./vendor/bin/phpcbf # fix code style  
./vendor/bin/rector # apply rector rules

# Run all checks               
composer check  # phpcs + psalm + phpstan
composer fix    # phpcbf + rector
```

Via Docker/Make:
```bash                                                               
make check   # run all static analysis tools
make tests   # run phpunit + infection mutation testing
```
