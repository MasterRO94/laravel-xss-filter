<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use MasterRO\LaravelXSSFilter\FilterXSS;
use MasterRO\LaravelXSSFilter\XSSFilterServiceProvider;
use MasterRO\LaravelXSSFilter\XSSCleanerFacade as XSSCleaner;

/**
 * Class FilterXSSTest
 *
 * @package MasterRO\LaravelXSSFilter\Tests
 */
class FilterXSSTest extends TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Get Package Providers
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            XSSFilterServiceProvider::class,
        ];
    }

    /**
     * Request
     *
     * @param array $data
     * @param string $url
     *
     * @return Request
     */
    protected function request($data = [], $url = 'https://example.test/store')
    {
        $this->request = Request::create($url, 'POST', $data);

        return $this->request;
    }

    /**
     * Response From Middleware With Input
     *
     * @param array $input
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    protected function responseFromMiddlewareWithInput($input = [])
    {
        return app(FilterXSS::class)
            ->handle($this->request($input), function () {
                // nothing to do here
            });
    }

    /**
     * @test
     */
    public function it_doesnt_change_non_html_inputs()
    {
        $this->responseFromMiddlewareWithInput($input = ['text' => 'Simple text', 'number' => 56]);

        $this->assertEquals($input, $this->request->all());
    }

    /**
     * @test
     */
    public function it_escapes_script_tags()
    {
        $this->responseFromMiddlewareWithInput([
            'with_src'  => 'Before text <script src="app.js"></script> after text',
            'multiline' => "Before text \n <script>\n let f = () => alert(1); f(); \n </script>\n After text",
        ]);

        $this->assertEquals([
            'with_src'  => 'Before text ' . e('<script src="app.js"></script>') . ' after text',
            'multiline' => "Before text \n " . e("<script>\n let f = () => alert(1); f(); \n </script>") . "\n After text",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_doesnt_change_non_script_html_inputs()
    {
        $this->responseFromMiddlewareWithInput([
            'html_with_script_src'       => '<div class="some-class"><a href="http://example.test" class="link">link text</a>Before text <script src="app.js"></script> after text</div> test on some text <span>test</span> <span style="color: red;">test</span> test',
            'html_with_script_multiline' => "<div class=\"some-class\">\n<a href=\"http://example.test\" class=\"link\">link text</a>\n Before text \n <script>\n let f = () => alert(1); f(); \n </script>\n After text</div> \n  test on some text <span>test</span> <span style='color: red;'>test</span> test",
        ]);

        $this->assertEquals([
            'html_with_script_src'       => '<div class="some-class"><a href="http://example.test" class="link">link text</a>Before text ' . e('<script src="app.js"></script>') . ' after text</div> test on some text <span>test</span> <span style="color: red;">test</span> test',
            'html_with_script_multiline' => "<div class=\"some-class\">\n<a href=\"http://example.test\" class=\"link\">link text</a>\n Before text \n " . e("<script>\n let f = () => alert(1); f(); \n </script>") . "\n After text</div> \n  test on some text <span>test</span> <span style='color: red;'>test</span> test",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_escapes_embed_elements()
    {
        $this->responseFromMiddlewareWithInput([
            'iframe'           => '<div class="block">Before text<iframe src="http://example.test">Not supported!</iframe> after text.</div>',
            'iframe_multiline' => '<div class="block">\nBefore text\n<iframe src="http://example.test">Not supported!</iframe>\n after text.\n</div>',
            'object'           => '<div class="block">Before text<object type="application/x-something"><param name="param_name" value="param_value"></object> after text.</div>',
            'object_multiline' => '<div class="block">\nBefore text\n<object type="application/x-something"><param name="param_name" value="param_value"></object>\n after text.\n</div>',
        ]);

        $this->assertEquals([
            'iframe'           => '<div class="block">Before text' . e('<iframe src="http://example.test">Not supported!</iframe>') . ' after text.</div>',
            'iframe_multiline' => '<div class="block">\nBefore text\n' . e('<iframe src="http://example.test">Not supported!</iframe>') . '\n after text.\n</div>',
            'object'           => '<div class="block">Before text' . e('<object type="application/x-something"><param name="param_name" value="param_value"></object>') . ' after text.</div>',
            'object_multiline' => '<div class="block">\nBefore text\n' . e('<object type="application/x-something"><param name="param_name" value="param_value"></object>') . '\n after text.\n</div>',
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_removes_inline_listeners()
    {
        $this->responseFromMiddlewareWithInput([
            'html'           => '<div class="hover" onhover=\'show()\' data-a="b"><p onclick="click"><span class="span" ondblclick="hide()"></span>Text ...</p></div>',
            'html_multiline' => "<div class=\"hover\" onhover=\"show()\" data-a=\"b\">\n<p onclick=\"click\">\n<span class=\"span\" ondblclick=\"hide()\"></span>Text ...</p>\n</div>",
        ]);

        $this->assertEquals([
            'html'           => '<div class="hover"  data-a="b"><p ><span class="span" ></span>Text ...</p></div>',
            'html_multiline' => "<div class=\"hover\"  data-a=\"b\">\n<p >\n<span class=\"span\" ></span>Text ...</p>\n</div>",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_removes_inline_listeners_from_invalid_html()
    {
        $this->responseFromMiddlewareWithInput([
            'html'           => '<div class="hover" onhover=show() onclick=ondblclick=alert() data-a="b"><p onclick=click><span class="span" ondblclick=hide()></span>Text ...</p></div>',
            'html_multiline' => "<div class=\"hover\" onhover=show() data-a=\"b\">\n<p onclick=click>\n<span class=span ondblclick=hide()></span>Text ...</p>\n</div>",
        ]);

        $this->assertEquals([
            'html'           => '<div class="hover" show() alert() data-a="b"><p click><span class="span" hide()></span>Text ...</p></div>',
            'html_multiline' => "<div class=\"hover\" show() data-a=\"b\">\n<p click>\n<span class=span hide()></span>Text ...</p>\n</div>",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_clears_nested_inputs()
    {
        $this->responseFromMiddlewareWithInput([
            'value1' => 'Value 1',
            'value2' => 2,
            'html'   => [
                'oneline'  => '<div class="hover" onhover="show()" data-a="b"><p onclick="click"><span class="span" ondblclick="hide()"></span>Text ...</p></div><div class="some-class"><a href="http://example.test" class="link">link text</a>Before text <script src="app.js"></script> after text</div>',
                'multline' => "<div class=\"hover\" onhover=\"show()\" data-a=\"b\">\n<p onclick=\"click\">\n<span class=\"span\" ondblclick=\"hide()\"></span>Text ...</p>\n</div>\n<div class=\"some-class\">\n<a href=\"http://example.test\" class=\"link\">link text</a>\n Before text \n <script>\n let f = () => alert(1); f(); \n </script>\n After text</div>",
            ],
            'value3' => [
                'value3_1' => 'Value 3-1',
                'value3_2' => 32,
            ],
        ]);

        $this->assertEquals([
            'value1' => 'Value 1',
            'value2' => 2,
            'html'   => [
                'oneline'  => '<div class="hover"  data-a="b"><p ><span class="span" ></span>Text ...</p></div><div class="some-class"><a href="http://example.test" class="link">link text</a>Before text ' . e('<script src="app.js"></script>') . ' after text</div>',
                'multline' => "<div class=\"hover\"  data-a=\"b\">\n<p >\n<span class=\"span\" ></span>Text ...</p>\n</div>\n<div class=\"some-class\">\n<a href=\"http://example.test\" class=\"link\">link text</a>\n Before text \n " . e("<script>\n let f = () => alert(1); f(); \n </script>") . "\n After text</div>",
            ],
            'value3' => [
                'value3_1' => 'Value 3-1',
                'value3_2' => 32,
            ],
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_dont_convert_0_to_empty_string()
    {
        $this->responseFromMiddlewareWithInput($input = ['text' => '0']);

        $this->assertEquals($input, $this->request->all());
    }

    /**
     * @test
     */
    public function it_removes_inline_javascript_in_href()
    {
        $this->responseFromMiddlewareWithInput([
            'html'           => '<div class="hover"><a href="javascript:alert(document.cookie)">Link</a></div>',
            'html_multiline' => "<div class=\"hover\">\n<p onclick=click>\n<a href=\"javascript:alert(document.cookie)\">Link</a>\n</p>\n</div>",
        ]);

        $this->assertEquals([
            'html'           => '<div class="hover"><a href="">Link</a></div>',
            'html_multiline' => "<div class=\"hover\">\n<p click>\n<a href=\"\">Link</a>\n</p>\n</div>",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_doest_not_touch_other_attributes()
    {
        $this->responseFromMiddlewareWithInput([
            'html'           => '<p><span onclick="alert(1)" data-toggle="popover" onclick=click data-placement="top" data-content="description">text</span></p>',
            'html_multiline' => "<p>\n<span onclick=\"alert(1)\" data-toggle=\"popover\" data-placement=\"top\" data-content=\"description\">\ntext\n</span>\n</p>",
        ]);

        $this->assertEquals([
            'html'           => '<p><span  data-toggle="popover" click data-placement="top" data-content="description">text</span></p>',
            'html_multiline' => "<p>\n<span  data-toggle=\"popover\" data-placement=\"top\" data-content=\"description\">\ntext\n</span>\n</p>",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_escapes_inline_event_listeners()
    {
        XSSCleaner::config()->setEscapeInlineListeners(true);

        $this->responseFromMiddlewareWithInput([
            'html'           => '<p><span onclick="alert(1)" data-toggle="popover" onclick=click data-placement="top" data-content="description">text</span></p>',
            'html_multiline' => "<p>\n<span onclick=\"alert(1)\" data-toggle=\"popover\" data-placement=\"top\" data-content=\"description\">\ntext\n</span>\n</p>",
        ]);

        $this->assertEquals([
            'html'           => '<p><span onclick&#x3d;"alert(1)" data-toggle="popover" onclick&#x3d;click data-placement="top" data-content="description">text</span></p>',
            'html_multiline' => "<p>\n<span onclick&#x3d;\"alert(1)\" data-toggle=\"popover\" data-placement=\"top\" data-content=\"description\">\ntext\n</span>\n</p>",
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_cleans_disallowed_media_hosts()
    {
        XSSCleaner::config()->allowElement('iframe')->allowMediaHosts(['youtube.com']);

        $this->responseFromMiddlewareWithInput([
            'iframe'           => '<div class="block">Before text<iframe src="http://example.test">Not supported!</iframe> after text.</div>',
            'iframe_multiline' => '<div class="block">\nBefore text\n<iframe src="http://example.test">Not supported!</iframe>\n after text.\n</div>',
            'video'            => '<div class="block">Before text<video><source src="https://video.test/play"></video> after text.</div>',
            'video_multiline'  => '<div class="block">\nBefore text\n<video>\n<source src="https://video.test/1/play">\n<source src="https://video.test/2/play"></video>\n after text.\n</div>',
        ]);

        $this->assertEquals([
            'iframe'           => '<div class="block">Before text<iframe src="#!">Not supported!</iframe> after text.</div>',
            'iframe_multiline' => '<div class="block">\nBefore text\n<iframe src="#!">Not supported!</iframe>\n after text.\n</div>',
            'video'            => '<div class="block">Before text<video><source src="#!"></video> after text.</div>',
            'video_multiline'  => '<div class="block">\nBefore text\n<video>\n<source src="#!">\n<source src="#!"></video>\n after text.\n</div>',
        ], $this->request->all());
    }

    /**
     * @test
     */
    public function it_does_not_escape_allowed_media_hosts()
    {
        XSSCleaner::config()->allowElement('iframe')->allowMediaHosts(['example.test', 'https://video.test', 'youtu.be']);

        $this->responseFromMiddlewareWithInput([
            'iframe'           => '<div class="block">Before text<iframe src="http://example.test">Not supported!</iframe> after text.</div>',
            'iframe_multiline' => '<div class="block">\nBefore text\n<iframe src="http://example.test">Not supported!</iframe>\n after text.\n</div>',
            'video'            => '<div class="block">Before text<video><source src="https://video.test/play"></video> after text.</div>',
            'video_multiline'  => '<div class="block">\nBefore text\n<video>\n<source src="https://video.test/1/play">\n<source src="//youtu.be/play"></video>\n after text.\n</div>',
        ]);

        $this->assertEquals([
            'iframe'           => '<div class="block">Before text<iframe src="http://example.test">Not supported!</iframe> after text.</div>',
            'iframe_multiline' => '<div class="block">\nBefore text\n<iframe src="http://example.test">Not supported!</iframe>\n after text.\n</div>',
            'video'            => '<div class="block">Before text<video><source src="https://video.test/play"></video> after text.</div>',
            'video_multiline'  => '<div class="block">\nBefore text\n<video>\n<source src="https://video.test/1/play">\n<source src="//youtu.be/play"></video>\n after text.\n</div>',
        ], $this->request->all());
    }
}
