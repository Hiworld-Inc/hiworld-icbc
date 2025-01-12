# Laravel ICBC

工商银行支付 SDK 的 Laravel 封装包。

## 安装

```bash
composer require hiworld/laravel-icbc
```

## 配置

1. 发布配置文件：

```bash
php artisan vendor:publish --tag=icbc-config
```

2. 在 `.env` 文件中添加以下配置：

```env
ICBC_APP_ID=your_app_id
ICBC_PRIVATE_KEY=your_private_key
ICBC_PUBLIC_KEY=icbc_public_key
ICBC_SIGN_TYPE=RSA
ICBC_CHARSET=UTF-8
ICBC_FORMAT=json
ICBC_ENCRYPT_KEY=your_encrypt_key
ICBC_ENCRYPT_TYPE=your_encrypt_type
ICBC_CA=your_ca
ICBC_PASSWORD=your_password
ICBC_SANDBOX=true  # 设置为 false 以使用生产环境
```

## 使用方法

### 方法 1：使用 Facade

```php
use Hiworld\LaravelIcbc\Facades\IcbcClient;
// 支付
$result = IcbcClient::pay([
    'order_no' => '123456',
    'amount' => '100.00',
    // 其他支付参数...
]);

// 查询订单
$result = IcbcClient::query([
    'order_no' => '123456'
]);

// 退款
$result = IcbcClient::refund([
    'order_no' => '123456',
    'refund_amount' => '100.00'
]);

// 撤销订单
$result = IcbcClient::cancel([
    'order_no' => '123456'
]);
```

### 方法 2：使用依赖注入

```php
use Hiworld\LaravelIcbc\Services\IcbcService;

class PaymentController extends Controller
{
    protected $icbc;
    
    public function __construct(IcbcService $icbc)
    {
        $this->icbc = $icbc;
    }
    
    public function pay()
    {
        $result = $this->icbc->pay([
            'order_no' => '123456',
            'amount' => '100.00',
            // 其他支付参数...
        ]);
        
        return $result;
    }
}
```

## 支持的方法

- `pay()` - 发起支付
- `query()` - 查询订单
- `refund()` - 退款
- `cancel()` - 撤销订单
- `execute()` - 执行自定义请求

## 注意事项

1. 请确保在生产环境中妥善保管各种密钥信息
2. 建议在正式使用前先在测试环境进行充分测试
3. 如需要其他接口支持，可以使用 `execute()` 方法自定义请求

## License

MIT 