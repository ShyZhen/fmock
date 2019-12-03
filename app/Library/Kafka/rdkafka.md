### kafka
```
Kafka 是一种高吞吐量的分布式发布订阅消息系统
```
### afka角色必知
```$xslt
producer：生产者。
consumer：消费者。
topic: 消息以topic为类别记录,Kafka将消息种子(Feed)分类, 每一类的消息称之为一个主题(Topic)。
broker：以集群的方式运行,可以由一个或多个服务组成，每个服务叫做一个broker;消费者可以订阅一个或多个主题(topic), 并从Broker拉数据,从而消费这些已发布的消息。
```

### 经典模型
- 一个主题下的分区不能小于消费者数量，即一个主题下消费者数量不能大于分区属，大了就浪费了空闲了
- 一个主题下的一个分区可以同时被不同消费组其中某一个消费者消费
- 一个主题下的一个分区只能被同一个消费组的一个消费者消费

### 常用参数说明
#### auto.offset.reset
```$xslt
1. earliest：自动将偏移重置为最早的偏移量
2. latest：自动将偏移量重置为最新的偏移量（默认）
3. none：如果consumer group没有发现先前的偏移量，则向consumer抛出异常。
4. 其他的参数：向consumer抛出异常（无效参数）
```


#### request.required.acks
```$xslt
Kafka producer的ack有3中机制，初始化producer时的producerconfig可以通过配置request.required.acks不同的值来实现。

0：这意味着生产者producer不等待来自broker同步完成的确认继续发送下一条（批）消息。此选项提供最低的延迟但最弱的耐久性保证（当服务器发生故障时某些数据会丢失，如leader已死，但producer并不知情，发出去的信息broker就收不到）。

1：这意味着producer在leader已成功收到的数据并得到确认后发送下一条message。此选项提供了更好的耐久性为客户等待服务器确认请求成功（被写入死亡leader但尚未复制将失去了唯一的消息）。

-1：这意味着producer在follower副本确认接收到数据后才算一次发送完成。 
此选项提供最好的耐久性，我们保证没有信息将丢失，只要至少一个同步副本保持存活。

三种机制，性能依次递减 (producer吞吐量降低)，数据健壮性则依次递增。
```

### 安装kafka
```$xslt
# 官方下载地址：http://kafka.apache.org/downloads
# wget https://www.apache.org/dyn/closer.cgi?path=/kafka/1.1.1/kafka_2.12-1.1.1.tgz
tar -xzf kafka_2.12-1.1.1.tgz
cd kafka_2.12-1.1.0
```

### 安装kafka的php扩展
```$xslt
# 先安装rdkfka库文件
git clone https://github.com/edenhill/librdkafka.git
cd librdkafka/
./configure 
make
sudo make install

git clone https://github.com/arnaud-lb/php-rdkafka.git
cd php-rdkafka
phpize
./configure
make all -j 5
sudo make install

vim [php]/php.ini
extension=rdkafka.so
```

### 文件说明
```$xslt
文件基类
/app/Library/Kafka/Rdkafka.php
生产类
app/Library/Kafka/Producer.php
消费类
app/Library/Kafka/Consumer.php
```

### 调用(持续更新中)
```$xslt
.env
KAFKA_BROKERS = 134.175.66.185:9092

生产
$config = [
            'ip'=>env('KAFKA_BROKERS'),
            'dr_msg_cb' => function($kafka, $message) {
                var_dump((array)$message);
            }
        ];
        $producer = new Producer($config);
        $rst = $producer->setBrokerServer()
            ->setProducerTopic('test')
            ->producer('qkl038', 90);

        var_dump($rst);

消费
$offset = 86; //开始消费点
        $consumer = new Consumer(['ip'=>env('KAFKA_BROKERS')]);
        $consumer->setConsumerGroup('myConsumerGroup')
            ->setBrokerServer(env('KAFKA_BROKERS'))
            ->setConsumerTopic()
            ->setTopic('test', 0, $offset)
            //->subscribe(['qkl01'])
            ->consumer(function($msg){
                var_dump($msg);die;
            });
```