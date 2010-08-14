<?php require "includes/project_info.php" ?>
<?php require "../includes/header.php" ?>
<?php include "../includes/advert.php" ?>

<h1>
Krati
</h1>

<p>
<strong>Krati</strong> is a simple persistent content data store with very low latency and high throughput.
It is designed for easy integration with other services with little effort
in tuning configuration, performance and JVM garbage collection.
This software is published under the terms of the Apache Software License version 2.0,
a copy of which has been included in the LICENSE file shipped with the Krati distribution.
</p>

<p>
<strong>Krati</strong> is a time measurement in Sanskrit and stands for 68,000th of one second.
Such a time measurement provides an ideal quantification for read/write latency and throughput
and also defines the performance goals of the Krati content data store.
</p>

<h2>
Design Considerations:
</h2>

<ul>
    <li> Data Model:
      <ul>
        <li> Varying-length data array</li>
        <li> Key-value data store</li>
      </ul>
    </li>
    <li> Multi-Reader and Single Writer 
       <ul>
         <li> Append-only writes</li>
         <li> Concurrent reads and writes</li>
     	</ul>
    </li>
    <li> Persistency:
       <ul>
          <li> Write-ahead redo log</li>
          <li> Writes persisted to disk in batch</li>
       </ul>
    </li>
    <li> Performance:
      <ul>
         <li> Low read/write latency</li>
         <li> High read/write throughput</li>
      </ul>
    </li>
    <li> Automatic data compaction</li>
    <li> Java-based:
      <ul>
         <li> Java Nio-enabled</li>
         <li> Java GC friendly</li>
      </ul>
    </li>
</ul>

<h2>
Architectural Overview:
</h2>
<p>
The conceptual architecture of Krati is composed of three layers.
The top layer is the content data store service API, which includes array-like set/get methods and standard key-value store get/put/delete methods.
The bottom layer provides Java NIO-based persistency to back up data segments, indexes, and meta data via disk files.
</p>

<p>
The layer in the middle manages data segments, data indexes, and write-ahead redo logic.
Segment Manager is responsible for segment creation, recycle and compaction.
Index Manager uses hash functions to map keys to memory-resident array indexes.
It does automatic batch-based flushing to sync data, indexes, and meta data to disk files.
Data Handler allows customization of data to put into segments.
</p>

<p>
Krati segments can be thought of pure data blocks backed by files on disk. Every segment contains a number of data elements.
The index manager provides logic for retrieving indexes to data elements in a segment.
Krati always keeps indexes in memory for better performance.
</p.

<p>
Krati supports three types of segments:
 <ul>
   <li><b>MemorySegement</b>:
   Memory resident and designed for extremely fast reads and writes.
   It works for small data sets that fit into memory.
   </li>
   <li><b>MappedSegement</b>:
   I/O page cache resident via Java/NIO mmap and designed for relatively fast reads and writes.
   It works for relatively large data sets that do not fit into memory.
   </li>
   <li><b>ChannelSegement</b>:
   I/O page cache resident and designed for relatively slow reads and writes.
   It works for very large data sets that cannot fit into memory.
   </li>
 </ul>
</p>

<p align="center">
  <img src="images/krati_architecture.jpg" />
</p>

<p>
The following diagram shows the internal implementation of the Krati main class SimpleDataArray.
Multiple readers can issue concurrent reads via DataArray get methods.
There is one and only one writer, which does append-only writes to data segments via DataArray set methods.
The writer periodically starts a compactor to perform segment compaction and reclaim wasted data space. 
</p>

<p align="center">
  <img src="images/krati_internal_architecture.jpg" />
</p>

<h2>
Getting Started:
</h2>

<p>
It is the best to go over a few lines of sample code to become familiar with Krati.
You can obtain the Krati distribution with versions 0.3.4 or above.
The <code>examples</code> directory from the distribution contains a number of sample files such as <code>KratiDataCache.java</code> and <code>KratiDataStore.java</code>.
</p>

<h2>
Performance Overview:
</h2>
This section provides a quick glance of Krati performance. The performance figures were collected using the setup below: 

<ul>
<li>Krati Configuration</li>
  <ul>
    <li>1 Writer </li>
    <li>4 Readers </li>
    <li>Data Size: 0.5~2 KB, Avg. 1 KB </li>
    <li>Batch Size: 10,000 </li>
    <li>Segment Size: 256 MB </li>
    <li>Member Count: 5,000,000 (typical partition size) </li>
  </ul>
<li>Test Machine</li>
  <ul>
    <li>Mac OS X Version: 10.5.8 </li>
    <li>Processor: 2 x 2.26 GHz Quad-Core Intel Xeon </li>
    <li>Memory: 24 GB 1066 MHz DDR3 </li>
    <li>Startup Disk: Macintosh HD </li>
  </ul>
<li>Java 6</li>
  <ul>
    <li>Sun Hotspot JVM </li>
    <li>-server -Xmx16G </li>
  </ul>
</ul>

<p>
The Krati distribution has included a number of tests for collecting performance statistics.
If you have a Krati distribution with versions 0.3.4 and above, you can simply run 
<code>
<span>ant test.loggc -Dtests.to.run=TestDataCache</span>
</code>
to collect read/write throughput and latency on your own computer.
If you want to evaluate DataCache with MappedSegment and ChannelSegment, you can run
<code>
<span>ant test.loggc -Dtests.to.run=TestDataCacheMapped</span>
</code>
and
<code>
<span>ant test.loggc -Dtests.to.run=TestDataCacheChannel</span>
</code>
respectively.
</p>

<p>
The write throughput is approximately 20~30 writes per millisecond for MemorySegment and roughly 10~20 writes per millisecond for MappedSegment and ChannelSegment.
The persistency and recovery achieved via disk files and redo logs have an impact on write throughput.
</p>

<p align="center">
  <img src = "images/krati_data_cache_write_throughput.jpg" width="500px" />
</p>

<p>
The read throughput is approximately 1000~1200 writes per reader thread per millisecond for MemorySegment. It is an order of magnitude faster than throughput obtained using MappedSegment or ChannelSegment.
</p>

<p align="center">
  <img src = "images/krati_data_cache_read_throughput.jpg" width="500px" />
</p>

<p>
The ChannelSegment has the highest write latency. As shown in the figure below, approximately 80% of writes have a latency between 10 and 50 microseconds.
The write latencies for MemorySegment and MappedSegment are approximately on the same level with the majority of writes finished between 1 and 10 microseconds.
</p>

<p align="center">
  <img src = "images/krati_data_cache_write_latency.jpg" width="500px" />
</p>

<p>
The read latency for MemorySegment is under 1 microsecond. In contrast, over 95% of reads from ChannelSegment range between 10 and 50 microseconds.
MappedSegment is in the middle.
</p>

<p align="center">
  <img src = "images/krati_data_cache_read_latency.jpg" width="500px" />
</p>

For an in-depth comparison of Krati and BDB JE, please refer to <a href="slides/krati_vs_bdb.pdf"><b>A Thorough Look at Krati vs. BDB JE - A Comparison of Throughput, Latency and GC</b></a>.

<?php require "../includes/footer.php" ?>