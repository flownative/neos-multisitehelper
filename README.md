[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Packagist](https://img.shields.io/packagist/v/flownative/neos-multisitehelper.svg)](https://packagist.org/packages/flownative/neos-multisitehelper)
[![Maintenance level: Friendship](https://img.shields.io/badge/maintenance-%E2%99%A1%E2%99%A1-ff69b4.svg)](https://www.flownative.com/en/products/open-source.html)

# Multisite Helper for Neos

The Neos Multisite Helper contains a tool to create asset collections for sites created by the Neos Multisite Kickstarter.

Furthermore it provides an authentication provider that gracefully denies login if a user has no access to a site she tries to log in to.

## Installation

Usually this package is required by a site package and thus installed along with the depending site automatically.

Only if you keep your site package in your Neos distribution (and not install it using composer), manual installation is needed:

`composer require flownative/neos-multisitehelper`

# Command Usage

After kickstarting and importing a site, use the following command:

`./flow multisite:setup --package-key Acme.AcmeCom`

This creates an asset collection with a name as expected by the kickstarted site and assign it as default collection to the site.

# Authentication Provider

The package comes with an authentication provider that is configured for Neos user accounts through the settings of this package.

It checks if access to the Site the user logged in is granted and rolls back authentication if needed. This avoids an error thrown by the security framework otherwise and thus provides a better user experience. For this to work, a domain must be assigned to the site – otherwise only users with the `Neos.Neos:Administrator` role are granted access.

# Credits

Development of this package has been sponsored by Schwabe AG, Muttenz, Switzerland.

The authentication provider was adapted from code Aske Ertmann provided in a blog post at https://blog.ertmann.me/multi-site-access-restriction-with-neos-cms-9d5624126d5b.
