GigyaCMS class
==============

Is a class with extra API functions and other utils,
which build especially for working with PHP CMS such as Wordpress.

The class contains 4 calls for constant variables which is
NOT defined in the file, and you MUST define in your code.

### For instance:
```PHP
// Gigya CMS
define( 'GIGYA__API_KEY', $this->options['api_key'] );
define( 'GIGYA__API_SECRET', $this->options['api_secret'] );
define( 'GIGYA__PRIVATE_KEY', $this->options['rsa_private_key'] );
define( 'GIGYA__API_DOMAIN', $this->options['data_center'] );