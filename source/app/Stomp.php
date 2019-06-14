<?php

namespace App;

use Stomp\Client;
use Stomp\Exception\StompException;
use Stomp\StatefulStomp;

class Stomp {

  private $host;
  private $port;
  private $username;
  private $password;
  private $vhostname;

  private $clientInstance;
  private $queue;

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param mixed $queue
     * @return Stomp
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }


    /**
     * Stomp constructor.
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @param $vhostname
     */
    public function __construct($host=null, $port=null, $username=null, $password=null, $vhostname='/', $queue = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhostname = $vhostname;
        $this->queue= $queue;

        $this->clientInstance = null;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     * @return Stomp
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return Stomp
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return Stomp
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Stomp
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVhostname()
    {
        return $this->vhostname;
    }

    /**
     * @param mixed $vhostname
     * @return Stomp
     */
    public function setVhostname($vhostname)
    {
        $this->vhostname = $vhostname;
        return $this;
    }

  private function stompClient() {
    if(is_null($this->clientInstance)) {
      $this->clientInstance = new Client('tcp://192.168.6.1:61613');
      $this->clientInstance->setLogin('mqadmin', 'isgtecnologia_');
      $this->clientInstance->setVhostname('/');
    }
    return($this->clientInstance);
  }
  
  public function sendMessage($message, $queueName = null) {

    if(!is_null($queueName)) {
      $this->setQueue($queueName);
    }
    $queueName = $this->getQueue();

    if (!is_string($message)) {
      $message = json_encode($message);
    }
      try {
          $stomp = $this->stompClient();
          $out = $stomp->send($queueName, $message, array('persistent' => 'true'));
      } catch (StompException $e) {
          echo $e->getMessage() . "\n";
      }

      $stomp->disconnect();
      return($out);
  }
  
  // public function consumer($queueName = null) {
  //   if(is_null($queueName)) {
  //       $queueName = $this->getQueue();
  //   }
  //   $iterations = 0;
  //   try {
  //     $stomp = new StatefulStomp($this->stompClient());
  //     $stomp->subscribe($queueName, null, 'client');
  //     do {
  //       $iterations++;
  //       $frame = $stomp->read();
  //       if(isset($frame) and is_object($frame) and property_exists($frame, 'body')) {
  //         // Processamento
  //         try {
  //           print_r(json_encode(json_decode($frame->body), JSON_PRETTY_PRINT));
  //         } catch (StompException $e) {
  //           echo "[ERROR] " . $e->getMessage() . "\n";
  //         }

  //         if(random_int(0, 5) % 2 == 0) {
  //             $stomp->ack($frame);
  //             echo "\n<< [200] Success", PHP_EOL;
  //         }
  //         else {
  //             $stomp->nack($frame);
  //             echo "\n<< [400] Processing error!!!", PHP_EOL;
  //         }
  //       }
  //     } while (True);
  
  //     $stomp->unsubscribe();
  //   } catch (StompException $e) {
  //     echo "[ERROR] producer cannot connect to RabbitMQ\n";
  //     echo $e->getMessage() . "\n";
  //   }
  // }

    public function consumeCallback($callback, $queueName = null) {
        if(is_null($queueName)) {
            $queueName = $this->getQueue();
        }
        $iterations = 0;
        try {
            $stomp = new StatefulStomp($this->stompClient());
            $stomp->subscribe($queueName, null, 'client');
            do {
                $iterations++;
                $frame = $stomp->read();
                if(isset($frame) and is_object($frame) and property_exists($frame, 'body')) {
                    // Processamento
                    try {
                        $callback(json_decode($frame->body));
                        $stomp->ack($frame);

                    } catch (StompException $e) {
                        $stomp->nack($frame);
                    }
                }
            } while (True);

            $stomp->unsubscribe();
        } catch (StompException $e) {
            echo $e->getMessage() . "\n";
        }
    }

}