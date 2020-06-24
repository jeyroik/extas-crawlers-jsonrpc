<?php
namespace tests;

/**
 * Class NotADefaultPlugin
 *
 * @jsonrpc_operation
 * @jsonrpc_name some.index
 * @jsonrpc_title Не стандартная операция
 * @jsonrpc_description Операция JSON RPC, со своей логикой
 * @jsonrpc_request_field name:string
 * @jsonroc_request_field parameters:array
 * @jsonrpc_response_field name:string
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class DocCommentNotADefaultPluginWith {}
