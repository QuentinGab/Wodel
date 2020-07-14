# wodel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

```
docs/
src/
tests/
```


## Install

Via Composer

``` bash
$ composer require QuentinGab/wodel
```

## Usage

``` php
$posts = QuentinGab\Models\Wodel::all();
foreach($posts as $post){
    echo $post->post_title;
}
```

``` php
$post = QuentinGab\Models\Wodel::current();
$post->post_title = "Hello World";
$post->save();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email quentin.gabriele@gmail.com instead of using the issue tracker.

## Credits

- [quentin gabriele][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/QuentinGab/wodel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/QuentinGab/wodel/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/QuentinGab/wodel.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/QuentinGab/wodel.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/QuentinGab/wodel.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/QuentinGab/wodel
[link-travis]: https://travis-ci.org/QuentinGab/wodel
[link-scrutinizer]: https://scrutinizer-ci.com/g/QuentinGab/wodel/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/QuentinGab/wodel
[link-downloads]: https://packagist.org/packages/QuentinGab/wodel
[link-author]: https://github.com/QuentinGab
[link-contributors]: ../../contributors
