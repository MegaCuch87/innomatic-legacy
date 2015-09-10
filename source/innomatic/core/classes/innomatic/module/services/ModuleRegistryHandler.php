<?php
namespace Innomatic\Module\Services;

/**
 * Module server socket handler and requests dispatcher.
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleRegistryHandler
{
   /**
     * registry structure.
     *
     * @var array
     * @since 5.1
     */
    private $registry;

    /**
     * address of the remote node with the up-to-date registry
     *
     * @var string
     * @since 5.1
     */
    private $r_address;

    /**
     * port of the remote node with the up-to-date registry
     *
     * @var string
     * @since 5.1
     */
    private $r_port;

     /**
     * returns Module registry in array format
     *
     * @since 5.1
     * @return array
     */
    public function getRegistry()
    {
        return $this->registry;
    }

     /**
     * Parses Module registry from local file or from remote peer
     *
     * @param string $host the host from which the registry has to be loaded (if null regitry is loaded locally)
     * @param string $port the port on the remote host (if null regitry is loaded locally)
     * @since 5.1
     * @return void
     */
    public function parseRegistry($host = null, $port = null)
    {
        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        $mode = $context->getConfig()->getKey('load_services_register');
         if($host == null || $port == null) {//loading registry for the first time
            if ($mode == 'local') {
                print('Module: services-extension loading netregistry locally'."\n");
                $this->loadLocalRegistry('modules-netregistry.xml');
            } elseif ($mode == 'remote'){
                $r_address = $context->getConfig()->getKey('remote_address');
                $r_port = $context->getConfig()->getKey('remote_port');
                print("Module: services-extension loading netregistry from $r_address:$r_port"."\n");
                $this->downloadRemoteRegistry($r_address,$r_port);
                $this->loadLocalRegistry('modules-netregistry-remote.xml');
            } else {
                Carthag::instance()->halt("error: check .ini file");
            }
        } else { //refreshing registry after a modification of the peers (es. ping timeout)
            print("Module: services-extension refreshing net-registry from $host:$port"."\n");
            $this->downloadRemoteRegistry($host,$port);
            $this->loadLocalRegistry('modules-netregistry-remote.xml');

        }
    }

    /**
     * Parses Module registry configuration from a local XML file
     * and stores it in a structured array.
     *
     * @param string $filename file that stores the registry
     * @since 5.1
     * @return void
     */
    private function loadLocalRegistry($filename)
    {
        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        $xmldoc = simplexml_load_file($context->getHome().'core/conf/'.$filename);
        $this->registry = array ();
        $nodename = 0;
        foreach ($xmldoc->node as $node) {
            $this->registry[$nodename]['address']="$node->address";
            $this->registry[$nodename]['port']="$node->port";
            $this->registry[$nodename]['load']="$node->load";
            $this->registry[$nodename]['ping_port']="$node->ping_port";
            foreach ($node->services->Modulemodule as $module) {
                $this->registry[$nodename]['services'][] = "$module";
            }
            $nodename++;
        }
//        var_dump($xmldoc);
//        var_dump($this->registry);
    }

    /**
     * Parses Module registry configuration from an XML file downloaded from a remote server;
     * then stores it in a structured array.
     *
     * @param string $host the host from which the registry has to be loaded
     * @param string $port the port on the remote host
     * @since 5.1
     * @return void
     */
    private function downloadRemoteRegistry($address, $port)
    {
 print("downloadRemoteRegistry: inizio\r\n");
         $result = '';
         try {
            $auth = \Innomatic\Module\Server\ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
            $socket = new \Innomatic\Net\Socket\Socket();
            $socket->connect($address, $port);
            $request = 'GET_REGISTRY Module/1.1'."\r\n";
            $request .= 'User: admin'."\r\n";
            $request .= 'Password: '.$auth->getPassword('admin')."\r\n";
print("Request content:\r\n".$request."\n");
            $socket->write($request);
            $result = trim($socket->readAll());
print("Result content:\r\n".$result."\n");
            $socket->disconnect();
         } catch (\Innomatic\Net\Socket\SocketException $e) {
            $result = "Module: services-extension cannot download up-to-date registry from $address:$port";
         }
         $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
         $file_registry = fopen($context->getHome().'core/conf/modules-netregistry-remote.xml', 'x');
         if($file_registry==false) {
            unlink($context->getHome().'core/conf/modules-netregistry-remote.xml');
            $file_registry = fopen($context->getHome().'core/conf/modules-netregistry-remote.xml', 'x');
         }

         fwrite($file_registry, $result);
         fclose($file_registry);
    }
}





/*example of registry content
 array(2) {
  ["127.0.0.1:9990"]=>
  array(3) {
    ["address"]=>
    string(9) "127.0.0.1"
    ["port"]=>
    string(4) "9990"
    ["services"]=>
    array(2) {
      [0]=>
      string(25) "io.innomatic.helloworldmodule"
      [1]=>
      string(11) "MyTestModule"
    }
  }
*/
