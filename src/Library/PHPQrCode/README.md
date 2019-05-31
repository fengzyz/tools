# 生成二维码
```
 public function test()
    {
        $fileName = time().'.png';
        $logoPath = storage_path('app/logo.png');
        $config = array(
            'ecc' => 'H',    // L-smallest, M, Q, H-best
            'size' => 12,    // 1-50
            'dest_file' => $fileName,
            'quality' => 90,
            'logo' => $logoPath,
            'logo_size' => 100,
            'logo_outline_size' => 20,
//            'logo_outline_color' => '#F0FFF0',
            'logo_radius' => 15,
            'logo_opacity' => 100,
        );
// 二维码内容
        $data = 'http://costalong.com';
// 创建二维码类
        $oPHPQRCode = new PHPQRCode();
// 设定配置
        $oPHPQRCode->setConfig($config);
// 创建二维码
        $qrcode = $oPHPQRCode->generate($data);
// 显示二维码
        echo '<img src="'.$qrcode.'?t='.time().'">';
    }
```