<?php
namespace tests;

/**
 * Class OperationWithDocComment
 *
 * @jsonrpc_operation
 *
 * @jsonrpc_name test
 * @jsonrpc_title Test
 * @jsonrpc_description This is operation for tests only
 *
 * @jsonrpc_request_field id:string
 * @jsonrpc_request_field name:string
 *
 * @jsonrpc_response_field id:int
 * @jsonrpc_response_field parameters:array
 *
 * @package tests
 * @author jeyroik <jeyroik@gmail.com>
 */
class DocCommentOperationWith
{

}
