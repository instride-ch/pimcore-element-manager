![Pimcore Element Manager](docs/images/github_banner.png)

### Requirements
* Pimcore `^10.0`

### Installation

- Install with composer
  ```
  composer require instride/pimcore-element-manager:^2.0
  ```

- Add to `config/bundles.php`
  ```php
    return [
        // ...
        Instride\Bundle\PimcoreElementManagerBundle\PimcoreElementManagerBundle::class => ['all' => true],
    ];
  ```
