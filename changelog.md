# Changelog

## 2.0.0 - 2017-05-18

* Bump up to superbalist/php-pubsub ^2.0
* Add new publishBatch method to KafkaPubSubAdapter

## 1.0.2 - 2016-09-08

* Fix consumer timing out whilst waiting for partition assignment

## 1.0.1 - 2016-09-07

* Switch to High Level Kafka Consumer
* Manually commit offsets in subscribe after callable is successful
* Fix travis build failing due to librdkafka & php-rdkafka not being installed

## 1.0.0 - 2016-09-02

* Initial release