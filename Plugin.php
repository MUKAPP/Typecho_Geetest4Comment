<?php

namespace TypechoPlugin\Geetest4Comment;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Widget\Options;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * Geetest4 评论验证插件
 *
 * @package Geetest4Comment
 * @author MUK
 * @version 1.0.0
 * @link https://mukapp.top
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        \Typecho\Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'addGeetestScript');
        \Typecho\Plugin::factory('Widget_Feedback')->comment = array(__CLASS__, 'validateGeetest');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
        $captchaId = new Text('captchaId', NULL, '', _t('极验验证码ID'), _t('输入你的极验验证码ID'));
        $form->addInput($captchaId);

        $captchaKey = new Text('captchaKey', NULL, '', _t('极验验证码Key'), _t('输入你的极验验证码Key'));
        $form->addInput($captchaKey);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 添加 Geetest 脚本到头部
     */
    public static function addGeetestScript()
    {
        echo '<script src="https://static.geetest.com/v4/gt4.js"></script>';
    }

    /**
     * 渲染 Geetest 验证码到评论表单
     */
    public static function commentCaptchaRender()
    {
        echo '
            <div id="captcha"></div>
            <input type="hidden" id="lot_number" name="lot_number">
            <input type="hidden" id="captcha_output" name="captcha_output">
            <input type="hidden" id="pass_token" name="pass_token">
            <input type="hidden" id="gen_time" name="gen_time">
            <script>
                var captchaInstance;
                initGeetest4({
                    captchaId: "' . Options::alloc()->plugin('Geetest4Comment')->captchaId . '",
                }, function (captcha) {
                    captchaInstance = captcha;
                    captcha.appendTo("#captcha");
                    captcha.onSuccess(function () {
                        var result = captcha.getValidate();
                        document.getElementById("lot_number").value = result.lot_number;
                        document.getElementById("captcha_output").value = result.captcha_output;
                        document.getElementById("pass_token").value = result.pass_token;
                        document.getElementById("gen_time").value = result.gen_time;
                    });
                    captcha.onError(function (error) {
                        alert("验证失败：" + error.msg + "，错误详情：" + error.desc.detail);
                        captcha.reset();
                    });
                });

                // 在表单提交时重置验证码
                $("#comment-form").submit(function () {
                    if (captchaInstance) {
                        captchaInstance.reset();
                    }
                });
            </script>
        ';
    }

    /**
     * 验证 Geetest 验证码
     *
     * @param array $comment
     * @return array
     * @throws \Exception
     */
    public static function validateGeetest(array $comment)
    {
        $lot_number = $_POST['lot_number'];
        $captcha_output = $_POST['captcha_output'];
        $pass_token = $_POST['pass_token'];
        $gen_time = $_POST['gen_time'];

        $captcha_id = Options::alloc()->plugin('Geetest4Comment')->captchaId;
        $captcha_key = Options::alloc()->plugin('Geetest4Comment')->captchaKey;
        $api_server = "http://gcaptcha4.geetest.com";

        $sign_token = hash_hmac('sha256', $lot_number, $captcha_key);

        $query = [
            'lot_number' => $lot_number,
            'captcha_output' => $captcha_output,
            'pass_token' => $pass_token,
            'gen_time' => $gen_time,
            'sign_token' => $sign_token,
        ];

        $url = $api_server . '/validate?captcha_id=' . $captcha_id;

        $response = file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($query),
            ]
        ]));

        $result = json_decode($response, true);

        if ($result['result'] !== 'success') {
            // 验证失败，抛出异常或返回错误信息
            throw new \Typecho\Widget\Exception(_t('验证码验证失败，请重试。'));
        }

        return $comment;
    }
}
