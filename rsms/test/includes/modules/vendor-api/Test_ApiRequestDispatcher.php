<?php
class Test_ApiRequestDispatcher implements I_Test {
    public function setup(){
        $this->dispatcher = new ApiRequestDispatcher([], []);
    }

    public function test__parse_api_request__allPath(){
        $params = [];
        $req = "/some_resource";
        $name = $this->dispatcher->parse_api_request($req, $params);
        Assert::eq($name, 'getAll', 'Path is converted to get-all function');
    }

    public function test__parse_api_request__withTrailingSlash_exception(){
        $params = [];
        $req = '/some_resource/';
        try{
            $name = $this->dispatcher->parse_api_request($req, $params);
            Assert::fail('Trailing slash did not result in exception');
        }
        catch(Exception $err){
            Assert::pass('Trailing slash results in error');
        }
    }

    public function test__parse_api_request__searchPath(){
        $params = [
            'name' => 'bob'
        ];

        $req = "/some_resource/search";
        $name = $this->dispatcher->parse_api_request($req, $params);
        Assert::eq($name, 'search', 'Path is converted to search function');
    }

    public function test__parse_api_request__infoPath(){
        $params = [];
        $req = "/some_resource/123";
        $name = $this->dispatcher->parse_api_request($req, $params);
        Assert::eq($name, 'getInfo', 'Path is converted to info function');
        Assert::eq( $params['id'], 123, 'Request parameter "123" was injected into request array');
    }

    public function test__parse_api_request__invalidId_exception(){
        $params = [];
        $req = "/some_resource/badIdValue";
        try{
            $name = $this->dispatcher->parse_api_request($req, $params);
            Assert::fail('Non-numeric ID path parameter did not result in exception');
        }
        catch(Exception $err){
            Assert::pass('Non-numeric ID path parameter resulted in exception');
        }
    }

    public function test__parse_api_request__emptyId_exception(){
        $params = [];
        $req = "/some_resource//detail";
        try{
            $name = $this->dispatcher->parse_api_request($req, $params);
            Assert::fail('Trailing slash did not result in exception');
        }
        catch(Exception $err){
            Assert::pass('Trailing slash results in error');
        }
    }

    public function test__parse_api_request__detailPath(){
        $params = [];
        $req = "/some_resource/123/detail";
        $name = $this->dispatcher->parse_api_request($req, $params);
        Assert::eq($name, 'getDetail', 'Path is converted to detail function');
    }
}
?>
