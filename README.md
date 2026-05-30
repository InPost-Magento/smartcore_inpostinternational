# InPost International for Magento 2

## Overview
Official InPost International integration module for Magento 2. The module allows seamless integration with InPost's international shipping services, enabling merchants to offer cross-border shipping solutions to their customers.

## Features
- International shipping through InPost carrier
- Integration with InPost Geowidget for pickup point selection 
- Automatic shipment creation
- Weight-based pricing system
- Parcel templates management
- Multiple shipping origin points support
- Advanced pickup management
- Customizable insurance options
- Order status updates
- Shipping labels generation
- Sandbox/Production environment support

## Requirements
- Magento 2.4.x
- PHP >= 8.1 < 8.6
- InPost International API credentials
- Geowidget token

## Installation

### Using Composer (recommended)
```bash
composer require smartcore/inpostinternational
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

### Manual Installation
1. Create directory `app/code/Smartcore/InPostInternational`
2. Download and extract module files to that directory
3. Run Magento installation commands:
```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

## Configuration
Basic configuration steps:
1. Get API credentials from InPost International
2. Go to Stores > Configuration > Sales > InPost International
3. Set up API credentials for desired environment (Sandbox/Production)
4. Configure sender details and shipping preferences

For detailed configuration instructions, please refer to the [documentation](docs/PL/DOCUMENTATION.md).

## Hyva checkout compatibility
To make it work with Hyva Checkout You need to install second module: smartcore/inpostinternationalhyva 
(available on [GitHub](https://github.com/InPost-Magento/smartcore_inpostinternationalhyva) 
and [Packagist](https://packagist.org/packages/smartcore/inpostinternationalhyva)). 
Please remember to enable backend validation in module configuration.

## Support
If you encounter any issues or need assistance:
- Check the detailed documentation in the `docs` folder
- Contact InPost International support
- Create an issue in the module's repository

## Security
If you discover any security related issues, please contact the InPost International team directly instead of using the issue tracker.

## Author
- Developed by Smartcore for InPost

## Release Notes
See [CHANGELOG.md](docs/CHANGELOG.md) for all version changes.

## Additional Resources
- [InPost International API Documentation](https://developers.inpost-group.com/)
- [InPost International Geowidget Documentation](https://dokumentacja-inpost.atlassian.net/wiki/spaces/PL/pages/481755145/Geowidget+International)
- [Module Documentation [PL]](docs/PL/DOCUMENTATION.md)
