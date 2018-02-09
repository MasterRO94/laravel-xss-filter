<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Orchestra\Testbench\TestCase;
use MasterRO\LaravelXSSFilter\FilterXSS;

class FilterXSSTest extends TestCase
{
	/**
	 * @var Request
	 */
	protected $request;


	/**
	 * @param array $data
	 * @param string $url
	 *
	 * @return Request
	 */
	protected function request($data = [], $url = 'https://example.test/store')
	{
		if (! $this->request) {
			$this->request = Request::create($url, 'POST', $data);
		}

		return $this->request;
	}


	/**
	 * @param array $input
	 *
	 * @return Response
	 */
	protected function responseFromMiddlewareWithInput($input = [])
	{
		return app(FilterXSS::class)->handle($this->request($input, true), function () {});
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
			'html_with_script_src'       => '<div class="some-class"><a href="http://example.test" class="link">link text</a>Before text <script src="app.js"></script> after text</div>',
			'html_with_script_multiline' => "<div class=\"some-class\">\n<a href=\"http://example.test\" class=\"link\">link text</a>\n Before text \n <script>\n let f = () => alert(1); f(); \n </script>\n After text</div>",
		]);

		$this->assertEquals([
			'html_with_script_src'       => '<div class="some-class"><a href="http://example.test" class="link">link text</a>Before text ' . e('<script src="app.js"></script>') . ' after text</div>',
			'html_with_script_multiline' => "<div class=\"some-class\">\n<a href=\"http://example.test\" class=\"link\">link text</a>\n Before text \n " . e("<script>\n let f = () => alert(1); f(); \n </script>") . "\n After text</div>",
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
			'html'           => '<div class="hover" onhover="show()" data-a="b"><p onclick="click"><span class="span" ondblclick="hide()"></span>Text ...</p></div>',
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

}
