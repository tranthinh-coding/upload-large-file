# This is my package upload-large-file

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tranthinh-coding/upload-large-file.svg?style=flat-square)](https://packagist.org/packages/tranthinh-coding/upload-large-file)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tranthinh-coding/upload-large-file/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tranthinh-coding/upload-large-file/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/tranthinh-coding/upload-large-file/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/tranthinh-coding/upload-large-file/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tranthinh-coding/upload-large-file.svg?style=flat-square)](https://packagist.org/packages/tranthinh-coding/upload-large-file)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require tranthinh-coding/upload-large-file
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="upload-large-file-config"
```

This is the contents of the published config file:

```php
return [
    /*
     |--------------------------------------------------------------------------
     | Đường dẫn lưu trữ file tạm thời
     |--------------------------------------------------------------------------
     |
     */
    'temp_path' => 'chunk-upload-temp',

    /*
     |
     | Thời gian hết hạn cho file tạm thời
     | Default: 24h
     */
    'chunk_expire' => 60 * 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Thư mục gốc lưu trữ file
    |--------------------------------------------------------------------------
    |
     */
    'dir_path' => 'uploads',

    /*
    |--------------------------------------------------------------------------
    | Quy tắc tạo thư mục con
    |--------------------------------------------------------------------------
    |
    | | 【Cài đặt chung】Phân theo năm, theo tháng, theo ngày, thư mục con hằng số.
    |
    | | Hỗ trợ các tùy chọn: 'year', 'month', 'date', 'const'
    |
    */
    'resource_subdir_rule' => 'month',

    'storage_disk' => 'local',

    'cache_driver' => 'file',

];
```

## Usage

```php
$uploadLargeFile = new Think\UploadLargeFile();

$uploadLargeFile->folder('uploads')
    ->upload($request);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Credits

- [Think](https://github.com/tranthinh-coding)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
