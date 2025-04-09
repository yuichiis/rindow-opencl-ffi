<?php
namespace RindowTest\OpenCL\FFI\BufferTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\Math\Matrix\OpenCL;
use Rindow\Math\Buffer\FFI\BufferFactory;
use Rindow\OpenCL\FFI\OpenCLFactory;

use TypeError;
use RuntimeException;

class BufferTest extends TestCase
{
    protected bool $skipDisplayInfo = true;
    //protected int $default_device_type = OpenCL::CL_DEVICE_TYPE_DEFAULT;
    //protected int $default_device_type = OpenCL::CL_DEVICE_TYPE_GPU;
    static protected int $default_device_type = OpenCL::CL_DEVICE_TYPE_GPU;

    public function newDriverFactory()
    {
        $factory = new OpenCLFactory();
        return $factory;
    }

    public function newContextFromType($ocl)
    {
        try {
            $context = $ocl->Context(self::$default_device_type);
        } catch(RuntimeException $e) {
            if(strpos('clCreateContextFromType',$e->getMessage())===null) {
                throw $e;
            }
            self::$default_device_type = OpenCL::CL_DEVICE_TYPE_DEFAULT;
            $context = $ocl->Context(self::$default_device_type);
        }
        return $context;
    }

    public function newHostBufferFactory()
    {
        $factory = new BufferFactory();
        return $factory;
    }

    public function testDevices()
    {
        $ocl = $this->newDriverFactory();
        $this->assertTrue($ocl->isAvailable());
        $platforms = $ocl->PlatformList();
        $m = $platforms->count();
        echo "Number of platforms($m)\n";
        for($p=0;$p<$m;$p++) {
            echo "Platform(".$p.")\n";
            echo "    CL_PLATFORM_NAME=".$platforms->getInfo($p,OpenCL::CL_PLATFORM_NAME)."\n";
            echo "    CL_PLATFORM_PROFILE=".$platforms->getInfo($p,OpenCL::CL_PLATFORM_PROFILE)."\n";
            echo "    CL_PLATFORM_VERSION=".$platforms->getInfo($p,OpenCL::CL_PLATFORM_VERSION)."\n";
            echo "    CL_PLATFORM_VENDOR=".$platforms->getInfo($p,OpenCL::CL_PLATFORM_VENDOR)."\n";
            echo "    CL_PLATFORM_EXTENSIONS=".$platforms->getInfo($p,OpenCL::CL_PLATFORM_EXTENSIONS)."\n";
            $devices = $ocl->DeviceList($platforms,index:$p);
            $n = $devices->count();
            echo "    Number of devices($n)\n";
            for($i=0;$i<$n;$i++) {
                echo "    device(".$i.")\n";
                echo "        CL_DEVICE_VENDOR_ID=".$devices->getInfo($i,OpenCL::CL_DEVICE_VENDOR_ID)."\n";
                echo "        CL_DEVICE_NAME=".$devices->getInfo($i,OpenCL::CL_DEVICE_NAME)."\n";
                echo "        CL_DEVICE_TYPE=(";
                $device_type = $devices->getInfo($i,OpenCL::CL_DEVICE_TYPE);
                if($device_type&OpenCL::CL_DEVICE_TYPE_CPU) { echo "CPU,"; }
                if($device_type&OpenCL::CL_DEVICE_TYPE_GPU) { echo "GPU,"; }
                if($device_type&OpenCL::CL_DEVICE_TYPE_ACCELERATOR) { echo "ACCEL,"; }
                if($device_type&OpenCL::CL_DEVICE_TYPE_CUSTOM) { echo "CUSTOM,"; }
                echo ")\n";
                echo "        CL_DEVICE_MAX_WORK_ITEM_SIZES=(".implode(',',$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_WORK_ITEM_SIZES)).")\n";
                echo "        CL_DEVICE_PARTITION_TYPE=(".implode(',',$devices->getInfo($i,OpenCL::CL_DEVICE_PARTITION_TYPE)).")\n";
                echo "        CL_DEVICE_PARTITION_PROPERTIES=(".implode(',',array_map(function($x){ return "0x".dechex($x);},
                    $devices->getInfo($i,OpenCL::CL_DEVICE_PARTITION_PROPERTIES))).")\n";
                echo "        CL_DEVICE_VENDOR=".$devices->getInfo($i,OpenCL::CL_DEVICE_VENDOR)."\n";
                echo "        CL_DEVICE_BUILT_IN_KERNELS=".$devices->getInfo($i,OpenCL::CL_DEVICE_BUILT_IN_KERNELS)."\n";
                echo "        CL_DEVICE_PROFILE=".$devices->getInfo($i,OpenCL::CL_DEVICE_PROFILE)."\n";
                echo "        CL_DRIVER_VERSION=".$devices->getInfo($i,OpenCL::CL_DRIVER_VERSION)."\n";
                echo "        CL_DEVICE_VERSION=".$devices->getInfo($i,OpenCL::CL_DEVICE_VERSION)."\n";
                echo "        CL_DEVICE_OPENCL_C_VERSION=".$devices->getInfo($i,OpenCL::CL_DEVICE_OPENCL_C_VERSION)."\n";
                echo "        CL_DEVICE_EXTENSIONS=".$devices->getInfo($i,OpenCL::CL_DEVICE_EXTENSIONS)."\n";
                echo "        CL_DEVICE_MAX_COMPUTE_UNITS=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_COMPUTE_UNITS)."\n";
                echo "        CL_DEVICE_MAX_WORK_ITEM_DIMENSIONS=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_WORK_ITEM_DIMENSIONS)."\n";
                echo "        CL_DEVICE_MAX_CLOCK_FREQUENCY=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_CLOCK_FREQUENCY)."\n";
                echo "        CL_DEVICE_ADDRESS_BITS=".$devices->getInfo($i,OpenCL::CL_DEVICE_ADDRESS_BITS)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_CHAR=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_CHAR)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_SHORT=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_SHORT)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_INT=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_INT)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_LONG=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_LONG)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_FLOAT=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_FLOAT)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_DOUBLE=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_DOUBLE)."\n";
                echo "        CL_DEVICE_PREFERRED_VECTOR_WIDTH_HALF=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_VECTOR_WIDTH_HALF)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_CHAR=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_CHAR)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_SHORT=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_SHORT)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_INT=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_INT)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_LONG=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_LONG)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_FLOAT=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_FLOAT)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_DOUBLE=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_DOUBLE)."\n";
                echo "        CL_DEVICE_NATIVE_VECTOR_WIDTH_HALF=".$devices->getInfo($i,OpenCL::CL_DEVICE_NATIVE_VECTOR_WIDTH_HALF)."\n";
                echo "        CL_DEVICE_MAX_READ_IMAGE_ARGS=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_READ_IMAGE_ARGS)."\n";
                echo "        CL_DEVICE_MAX_WRITE_IMAGE_ARGS=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_WRITE_IMAGE_ARGS)."\n";
                echo "        CL_DEVICE_MAX_SAMPLERS=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_SAMPLERS)."\n";
                echo "        CL_DEVICE_MEM_BASE_ADDR_ALIGN=".$devices->getInfo($i,OpenCL::CL_DEVICE_MEM_BASE_ADDR_ALIGN)."\n";
                echo "        CL_DEVICE_MIN_DATA_TYPE_ALIGN_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_MIN_DATA_TYPE_ALIGN_SIZE)."\n";
                echo "        CL_DEVICE_GLOBAL_MEM_CACHELINE_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_GLOBAL_MEM_CACHELINE_SIZE)."\n";
                echo "        CL_DEVICE_MAX_CONSTANT_ARGS=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_CONSTANT_ARGS)."\n";
                echo "        CL_DEVICE_PARTITION_MAX_SUB_DEVICES=".$devices->getInfo($i,OpenCL::CL_DEVICE_PARTITION_MAX_SUB_DEVICES)."\n";
                echo "        CL_DEVICE_REFERENCE_COUNT=".$devices->getInfo($i,OpenCL::CL_DEVICE_REFERENCE_COUNT)."\n";
                echo "        CL_DEVICE_GLOBAL_MEM_CACHE_TYPE=".$devices->getInfo($i,OpenCL::CL_DEVICE_GLOBAL_MEM_CACHE_TYPE)."\n";
                echo "        CL_DEVICE_LOCAL_MEM_TYPE=".$devices->getInfo($i,OpenCL::CL_DEVICE_LOCAL_MEM_TYPE)."\n";
                echo "        CL_DEVICE_MAX_MEM_ALLOC_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_MEM_ALLOC_SIZE)."\n";
                echo "        CL_DEVICE_GLOBAL_MEM_CACHE_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_GLOBAL_MEM_CACHE_SIZE)."\n";
                echo "        CL_DEVICE_GLOBAL_MEM_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_GLOBAL_MEM_SIZE)."\n";
                echo "        CL_DEVICE_MAX_CONSTANT_BUFFER_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_CONSTANT_BUFFER_SIZE)."\n";
                echo "        CL_DEVICE_LOCAL_MEM_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_LOCAL_MEM_SIZE)."\n";
                echo "        CL_DEVICE_IMAGE_SUPPORT=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE_SUPPORT)."\n";
                echo "        CL_DEVICE_ERROR_CORRECTION_SUPPORT=".$devices->getInfo($i,OpenCL::CL_DEVICE_ERROR_CORRECTION_SUPPORT)."\n";
                echo "        CL_DEVICE_HOST_UNIFIED_MEMORY=".$devices->getInfo($i,OpenCL::CL_DEVICE_HOST_UNIFIED_MEMORY)."\n";
                echo "        CL_DEVICE_ENDIAN_LITTLE=".$devices->getInfo($i,OpenCL::CL_DEVICE_ENDIAN_LITTLE)."\n";
                echo "        CL_DEVICE_AVAILABLE=".$devices->getInfo($i,OpenCL::CL_DEVICE_AVAILABLE)."\n";
                echo "        CL_DEVICE_COMPILER_AVAILABLE=".$devices->getInfo($i,OpenCL::CL_DEVICE_COMPILER_AVAILABLE)."\n";
                echo "        CL_DEVICE_LINKER_AVAILABLE=".$devices->getInfo($i,OpenCL::CL_DEVICE_LINKER_AVAILABLE)."\n";
                echo "        CL_DEVICE_PREFERRED_INTEROP_USER_SYNC=".$devices->getInfo($i,OpenCL::CL_DEVICE_PREFERRED_INTEROP_USER_SYNC)."\n";
                echo "        CL_DEVICE_MAX_WORK_GROUP_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_WORK_GROUP_SIZE)."\n";
                echo "        CL_DEVICE_IMAGE2D_MAX_WIDTH=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE2D_MAX_WIDTH)."\n";
                echo "        CL_DEVICE_IMAGE2D_MAX_HEIGHT=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE2D_MAX_HEIGHT)."\n";
                echo "        CL_DEVICE_IMAGE3D_MAX_WIDTH=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE3D_MAX_WIDTH)."\n";
                echo "        CL_DEVICE_IMAGE3D_MAX_HEIGHT=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE3D_MAX_HEIGHT)."\n";
                echo "        CL_DEVICE_IMAGE3D_MAX_DEPTH=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE3D_MAX_DEPTH)."\n";
                echo "        CL_DEVICE_IMAGE_MAX_BUFFER_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE_MAX_BUFFER_SIZE)."\n";
                echo "        CL_DEVICE_IMAGE_MAX_ARRAY_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_IMAGE_MAX_ARRAY_SIZE)."\n";
                echo "        CL_DEVICE_MAX_PARAMETER_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_MAX_PARAMETER_SIZE)."\n";
                echo "        CL_DEVICE_PROFILING_TIMER_RESOLUTION=".$devices->getInfo($i,OpenCL::CL_DEVICE_PROFILING_TIMER_RESOLUTION)."\n";
                echo "        CL_DEVICE_PRINTF_BUFFER_SIZE=".$devices->getInfo($i,OpenCL::CL_DEVICE_PRINTF_BUFFER_SIZE)."\n";
                echo "        CL_DEVICE_SINGLE_FP_CONFIG=(";
                $config = $devices->getInfo($i,OpenCL::CL_DEVICE_SINGLE_FP_CONFIG);
                if($config&OpenCL::CL_FP_DENORM) { echo "DENORM,"; }
                if($config&OpenCL::CL_FP_INF_NAN) { echo "INF_NAN,"; }
                if($config&OpenCL::CL_FP_ROUND_TO_NEAREST) { echo "ROUND_TO_NEAREST,"; }
                if($config&OpenCL::CL_FP_ROUND_TO_ZERO) { echo "ROUND_TO_ZERO,"; }
                if($config&OpenCL::CL_FP_ROUND_TO_INF) { echo "ROUND_TO_INF,"; }
                if($config&OpenCL::CL_FP_FMA) { echo "FMA,"; }
                if($config&OpenCL::CL_FP_SOFT_FLOAT) { echo "SOFT_FLOAT,"; }
                if($config&OpenCL::CL_FP_CORRECTLY_ROUNDED_DIVIDE_SQRT) { echo "CORRECTLY_ROUNDED_DIVIDE_SQRT,"; }
                echo ")\n";
                echo "        CL_DEVICE_DOUBLE_FP_CONFIG=(";
                $config = $devices->getInfo($i,OpenCL::CL_DEVICE_DOUBLE_FP_CONFIG);
                if($config&OpenCL::CL_FP_DENORM) { echo "DENORM,"; }
                if($config&OpenCL::CL_FP_INF_NAN) { echo "INF_NAN,"; }
                if($config&OpenCL::CL_FP_ROUND_TO_NEAREST) { echo "ROUND_TO_NEAREST,"; }
                if($config&OpenCL::CL_FP_ROUND_TO_ZERO) { echo "ROUND_TO_ZERO,"; }
                if($config&OpenCL::CL_FP_ROUND_TO_INF) { echo "ROUND_TO_INF,"; }
                if($config&OpenCL::CL_FP_FMA) { echo "FMA,"; }
                if($config&OpenCL::CL_FP_SOFT_FLOAT) { echo "SOFT_FLOAT,"; }
                if($config&OpenCL::CL_FP_CORRECTLY_ROUNDED_DIVIDE_SQRT) { echo "CORRECTLY_ROUNDED_DIVIDE_SQRT,"; }
                echo ")\n";
                echo "        CL_DEVICE_EXECUTION_CAPABILITIES=(";
                $config = $devices->getInfo($i,OpenCL::CL_DEVICE_EXECUTION_CAPABILITIES);
                if($config&OpenCL::CL_EXEC_KERNEL) { echo "KERNEL,"; }
                if($config&OpenCL::CL_EXEC_NATIVE_KERNEL) { echo "NATIVE_KERNEL,"; }
                echo ")\n";
                echo "        CL_DEVICE_QUEUE_PROPERTIES=(";
                $config = $devices->getInfo($i,OpenCL::CL_DEVICE_QUEUE_PROPERTIES);
                if($config&OpenCL::CL_QUEUE_OUT_OF_ORDER_EXEC_MODE_ENABLE) { echo "OUT_OF_ORDER_EXEC_MODE_ENABLE,"; }
                if($config&OpenCL::CL_QUEUE_PROFILING_ENABLE) { echo "PROFILING_ENABLE,"; }
                echo ")\n";
                echo "        CL_DEVICE_PARENT_DEVICE=(";
                $parent_device = $devices->getInfo($i,OpenCL::CL_DEVICE_PARENT_DEVICE);
                if($parent_device) {
                    echo "        deivces(".$parent_device->count().")\n";
                    for($j=0;$j<$parent_device->count();$j++) {
                        echo "            CL_DEVICE_NAME=".$parent_device->getInfo($j,OpenCL::CL_DEVICE_NAME)."\n";
                        echo "            CL_DEVICE_VENDOR=".$parent_device->getInfo($j,OpenCL::CL_DEVICE_VENDOR)."\n";
                        echo "            CL_DEVICE_TYPE=(";
                        $device_type = $parent_device->getInfo($j,OpenCL::CL_DEVICE_TYPE);
                        if($device_type&OpenCL::CL_DEVICE_TYPE_CPU) { echo "CPU,"; }
                        if($device_type&OpenCL::CL_DEVICE_TYPE_GPU) { echo "GPU,"; }
                        if($device_type&OpenCL::CL_DEVICE_TYPE_ACCELERATOR) { echo "ACCEL,"; }
                        if($device_type&OpenCL::CL_DEVICE_TYPE_CUSTOM) { echo "CUSTOM,"; }
                        echo ")\n";
                    }
                }
                echo ")\n";
            }
        }
    }

    public function testIsAvailable()
    {
        $ocl = $this->newDriverFactory();
        $this->assertTrue($ocl->isAvailable());
    }

    /**
     * construct Pure buffer
     */
    public function testConstructBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        
        $queue = $ocl->CommandQueue($context);
        $hostBuffer = $this->newHostBufferFactory()->Buffer(
            16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = $value;
        }
        
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE);
        $this->assertTrue($buffer->dtype()==0);
        $this->assertTrue($buffer->value_size()==0);
        $buffer->write($queue,$hostBuffer);
        $this->assertTrue($buffer->dtype()==NDArray::float32);
        $this->assertTrue($buffer->value_size()==(32/8));
    }

    /**
     * construct Pure buffer
     */
    public function testConstructPureBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $hostBuffer = $this->newHostBufferFactory()->Buffer(
            16,NDArray::float32
        );
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = $value;
        }
        
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE
        );
        $this->assertTrue($buffer->dtype()==0);
        $this->assertTrue($buffer->value_size()==0);
        $buffer->write($queue,$hostBuffer);
        $this->assertTrue($buffer->dtype()==NDArray::float32);
        $this->assertTrue($buffer->value_size()==(32/8));
    }

    /**
     * construct buffer with null
     */
    public function testConstructBufferWithNull()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            $flags=0,$htbuffer=null,$offset=0);
        $this->assertTrue(true);
    }

    /**
     * Constract use host buffer
     */
    public function testConstructBufferWithHostBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();
        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = $value;
        }
        
        $buffer = $ocl->Buffer(
            $context,intval(16*32/8),
            OpenCL::CL_MEM_USE_HOST_PTR,$hostBuffer
        );
        $this->assertTrue($buffer->dtype()==NDArray::float32);
        $this->assertTrue($buffer->value_size()==(32/8));
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $buffer->read($queue,$newHostBuffer);
        foreach(range(0,15) as $value) {
            $this->assertTrue($newHostBuffer[$value] == $value);
        }
    }

    /**
     * Type constraint
     */
    public function testTypeConstraint()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);

        $this->expectException(TypeError::class);
        //$this->expectExceptionMessage('??????????????????');

        $invalidBuffer = new \stdClass();
        $buffer = $ocl->Buffer(
            $context,intval(16*32/8),
            OpenCL::CL_MEM_USE_HOST_PTR,$invalidBuffer
        );
    }

    /**
     * Blocking read buffer
     */
    public function testBlockingReadBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();
        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = $value;
        }
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);
        $this->assertTrue($buffer->dtype()==NDArray::float32);
        $this->assertTrue($buffer->value_size()==(32/8));
        
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $newHostBuffer[$value]=0;
        }
        $buffer->read($queue,$newHostBuffer);
        foreach(range(0,15) as $value) {
            $this->assertTrue($newHostBuffer[$value] == $value);
        }
    }

    /**
     * Non-Blocking read buffer
     */
    public function testNonBlockingReadBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        for($i=0;$i<16;$i++) {
            $hostBuffer[$i]=$i;
        }
        $buffer = $ocl->Buffer(
            $context,
            intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );
        $hostBuffer2 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        for($i=0;$i<16;$i++) {
            $hostBuffer2[$i]=$i*2;
        }
        $buffer2 = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer2);
        for($i=0;$i<16;$i++) {
            $hostBuffer[$i]=0;
            $hostBuffer2[$i]=0;
        }
        $events = $ocl->EventList();
        $buffer->read($queue,$hostBuffer,
                $size=0,$offset=0,$host_offset=0,$blocking_read=false,$events);
        $buffer2->read($queue,$hostBuffer2,
                $size=0,$offset=0,$host_offset=0,$blocking_read=false,$events);
        $events->wait();
        for($i=0;$i<16;$i++) {
            $this->assertTrue($hostBuffer[$i] == $i);
            $this->assertTrue($hostBuffer2[$i] == $i*2);
        }
    }

    /**
     * Blocking read with null arguments
     */
    public function testBlockingReadWithNullArguments()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();
        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = $value;
        }
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);

        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $newHostBuffer[$value]=0;
        }
        $buffer->read($queue,$newHostBuffer,
                        $size=0,$offset=0,$host_offset=0,$blocking_read=null,$events=null,$waitEvent=null);
        for($i=0;$i<16;$i++) {
            $this->assertTrue($newHostBuffer[$i] == $i);
        }
    }

    /**
     * read with invalid object arguments
     */
    public function testReadWithInvalidObjectArguments()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $queue = $ocl->CommandQueue($context);

        $buffer = $ocl->Buffer(
            $context,
            intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE,
        );
        //$this->expectException(Throwable::class);
        $this->expectException(TypeError::class);
        //$this->expectExceptionMessage('????????????');

        $invalidBuffer = new \stdClass();
        $buffer->read($queue,$invalidBuffer);
    }

    /**
     * Blocking write buffer
     */
    public function testBlockingWriteBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        
        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = 0;
        }
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);

        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = $i+10;
        }

        $buffer->write($queue,$hostBuffer);
        $buffer->read($queue,$newHostBuffer);
        for($i=0;$i<16;$i++) {
            $this->assertTrue($newHostBuffer[$i] == $i+10);
        }
    }

    /**
     * Non-Blocking write buffer
     */
    public function testNonBlockingWriteBuffer()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $hostBuffer2 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $newHostBuffer2 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        
        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = 0;
        }
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );
        $hostBuffer2 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        for($i=0;$i<16;$i++) {
            $hostBuffer2[$i]=0;
        }
        $buffer2 = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer2
        );

        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = $i+20;
            $hostBuffer2[$i] = $i*3;
        }
        $events = $ocl->EventList();
        $buffer->write($queue,$hostBuffer,
                        $size=0,$offset=0,$host_offset=0,$blocking_write=false,$events);
        $buffer2->write($queue,$hostBuffer2,
                        $size=0,$offset=0,$host_offset=0,$blocking_write=false,$events);
        $events->wait();
        $buffer->read($queue,$newHostBuffer);
        $buffer2->read($queue,$newHostBuffer2);
        for($i=0;$i<16;$i++) {
            $this->assertTrue($newHostBuffer[$i] == $i+20);
            $this->assertTrue($newHostBuffer2[$i] == $i*3);
        }
    }

    /**
     * blocking write with null argments
     */
    public function testBlockingWriteWithNullArguments()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = 0;
        }
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);

        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = $i+20;
        }
        $buffer->write($queue,$hostBuffer,
                        $size=0,$offset=0,$host_offset=0,$blocking_write=null,$events=null,$waitEvent=null);
        $buffer->read($queue,$newHostBuffer);
        for($i=0;$i<16;$i++) {
            $this->assertTrue($newHostBuffer[$i] == $i+20);
        }
                
    }

    /**
     * write with invalid object arguments
     */
    public function testWriteWithInvalidObjectArguments()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $queue = $ocl->CommandQueue($context);
        $buffer = $ocl->Buffer(
            $context,
            intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE,
        );

        //$this->expectException(Throwable::class);
        $this->expectException(TypeError::class);
        //$this->expectExceptionMessage('???????????????????');

        $invalidBuffer = new \stdClass();
        $buffer->write($queue,$invalidBuffer);
    }

    /**
     * read and write buffer with wait event list
     */
    public function testReadAndWriteBufferWithWaitEventList()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $hostBuffer2 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $hostBuffer3 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $hostBuffer4 = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $newHostBuffer2 = $newHostBufferFactory->Buffer(16,NDArray::float32);

        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = 0;
        }
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);
        $buffer2 = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);

        for($i=0;$i<16;$i++) {
        }
        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = $i+30;
            $hostBuffer2[$i] = $i*3;
            $hostBuffer3[$i] = $i+40;
            $hostBuffer4[$i] = $i*4;
        }
        $write_events = $ocl->EventList();
        $buffer->write($queue,$hostBuffer,
                $size=0,$offset=0,$host_offset=0,$blocking_write=false,$write_events);
        $buffer2->write($queue,$hostBuffer2,
                $size=0,$offset=0,$host_offset=0,$blocking_write=false,$write_events);
        
        $read_events = $ocl->EventList();
        $buffer->read($queue,$newHostBuffer,
                $size=0,$offset=0,$host_offset=0,$blocking_read=false,$read_events,$write_events);
        $buffer2->read($queue,$newHostBuffer2,
                $size=0,$offset=0,$host_offset=0,$blocking_read=false,$read_events,$write_events);
        
        $write_events2 = $ocl->EventList();
        $buffer->write($queue,$hostBuffer3,
                $size=0,$offset=0,$host_offset=0,$blocking_write=false,$write_events2,$read_events);
        $buffer2->write($queue,$hostBuffer4,
                $size=0,$offset=0,$host_offset=0,$blocking_write=false,$write_events2,$read_events);
        $write_events2->wait();
        for($i=0;$i<16;$i++) {
            $this->assertTrue($newHostBuffer[$i] == $i+30);
            $this->assertTrue($newHostBuffer2[$i] == $i*3);
        }
        $queue->finish();
    }

    /**
     * fill
     */
    public function testFillDefaults()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        if($isOpenCL110) {
            $this->markTestSkipped('Unsuppored fill function on OpenCL 1.1');
            return;
        }
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = 0;
        }
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );

        $pattern = $newHostBufferFactory->Buffer(1,NDArray::float32);
        $pattern[0] = 123.5;
        $buffer->fill($queue,$pattern);
        $queue->finish();
        $buffer->read($queue,$newHostBuffer);
        foreach(range(0,15) as $value) {
            $this->assertTrue($newHostBuffer[$value] == 123.5);
        }

    }

    /**
     * fill with null arguments
     */
    public function testFillWithNullArguments()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        if($isOpenCL110) {
            $this->markTestSkipped('Unsuppored fill function on OpenCL 1.1');
            return;
        }
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = 0;
        }
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );

        $pattern = $newHostBufferFactory->Buffer(1,NDArray::float32);
        $pattern[0] = 123.5;
        $buffer->fill($queue,$pattern,
            $size=0,$offset=0,$pattern_size=0,$pattern_offset=0,$events=null,$waitEvent=null);

        $queue->finish();
        $buffer->read($queue,$newHostBuffer);
        foreach(range(0,15) as $value) {
            $this->assertTrue($newHostBuffer[$value] == 123.5);
        }

    }

    /**
     * fill with invalid object arguments
     */
    public function testFillWithInvalidObjectArguments()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        if($isOpenCL110) {
            $this->markTestSkipped('Unsuppored fill function on OpenCL 1.1');
            return;
        }
        $queue = $ocl->CommandQueue($context);
        $buffer = $ocl->Buffer(
            $context,
            intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE,
        );

        //$this->expectException(Throwable::class);
        $this->expectException(TypeError::class);
        //$this->expectExceptionMessage('??????????????');

        $invalidBuffer = new \stdClass();
        $buffer->fill($queue,$invalidBuffer);
    }

    /**
     * copy with defaults
     */
    public function testCopyDefault()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        if($isOpenCL110) {
            $this->markTestSkipped('Unsuppored fill function on OpenCL 1.1');
            return;
        }
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = 0;
        }
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);
        $buffer2 = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);

        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = 123+($i%2);
        }
        $buffer->write($queue,$hostBuffer);
        
        $buffer2->copy($queue,$buffer);
        $queue->finish();
        $buffer2->read($queue,$hostBuffer);
        foreach(range(0,15) as $value) {
            $this->assertTrue($hostBuffer[$value] == 123+($value%2));
        }
        
    }

    /**
     * copy with Null Arguments
     */
    public function testCopyWithNullArguemts()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        if($isOpenCL110) {
            $this->markTestSkipped('Unsuppored fill function on OpenCL 1.1');
            return;
        }
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        foreach(range(0,15) as $value) {
            $hostBuffer[$value] = 0;
        }
        $newHostBuffer = $newHostBufferFactory->Buffer(16,NDArray::float32);
        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);
        $buffer2 = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer);

        for($i=0;$i<16;$i++) {
            $hostBuffer[$i] = 123+($i%2);
        }
        $buffer->write($queue,$hostBuffer);
        
        $buffer2->copy($queue,$buffer,
            $size=0,$offset=0,$src_offset=0,$events=null,$waitEvent=null);

        $queue->finish();
        $buffer2->read($queue,$hostBuffer);
        foreach(range(0,15) as $value) {
            $this->assertTrue($hostBuffer[$value] == 123+($value%2));
        }
        
    }

    /**
     * construct with explicit dtype
     */
    public function testConstructWithExplicitDtype()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);

        $buffer = $ocl->Buffer($context,intval(16*32/8),
            OpenCL::CL_MEM_READ_WRITE,
            null,0,NDArray::float32);
    
        $this->assertTrue($buffer->dtype()==NDArray::float32);
        $this->assertTrue($buffer->value_size()==intval(32/8));
        
    }

    /**
     * readRect
     */
    public function testReadRect()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(3*3*3,NDArray::float32);
        $data = [ 
            99,99,99,  99,99,99,  99,99,99,
            99,99,99,  99, 1, 2,  99, 3, 4,
            99,99,99,  99, 5, 6,  99, 7, 8,
        ];
        foreach($data as $idx => $value) {
            $hostBuffer[$idx] = $value;
        }
        $buffer = $ocl->Buffer(
            $context,
            $hostBuffer->value_size()*count($hostBuffer),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );

        $subHostBuffer = $newHostBufferFactory->Buffer(2*2*2,NDArray::float32);
        $value_size = $subHostBuffer->value_size();
        $buffer->readRect(
            $queue,$subHostBuffer,[2*$value_size,2,2],
            $hostBufferOffset=0,
            $bufferOffsets=[1*$value_size,1,1],
            null,
            $buffer_row_pitch=$value_size*3,$buffer_slice_pitch=$value_size*3*3,
            $host_row_pitch=$value_size*2,$host_slice_pitch=$value_size*2*2,
            $blocking_read=true
        );
        $trues = [
            1,2, 3,4,
            5,6, 7,8
        ];
        foreach($trues as $idx => $value) {
            $this->assertTrue($subHostBuffer[$idx] == $value);
        }
        
    }

    /**
     * writeRect
     */
    public function testWriteRect()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(3*3*3,NDArray::float32);
        $size = count($hostBuffer);
        for($i=0;$i<$size;$i++) {
            $hostBuffer[$i] = 99;
        }
        $buffer = $ocl->Buffer(
            $context,
            $hostBuffer->value_size()*count($hostBuffer),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );

        $subHostBuffer = $newHostBufferFactory->Buffer(2*2*2,NDArray::float32);
        $value_size = $subHostBuffer->value_size();
        $data = [ 
            -1,-2, -3,-4,
            -5,-6, -7,-8
        ];
        foreach($data as $idx => $value) {
          $subHostBuffer[$idx] = $value;
        }
        $buffer->writeRect(
            $queue,$subHostBuffer,[2*$value_size,2,2],
            $hostBufferOffset=0,
            $bufferOffsets=[1*$value_size,1,1],
            null,
            $buffer_row_pitch=$value_size*3,$buffer_slice_pitch=$value_size*3*3,
            $host_row_pitch=$value_size*2,$host_slice_pitch=$value_size*2*2,
            $blocking_write=true
        );
        $trues = [
            99,99,99,  99,99,99,  99,99,99,
            99,99,99,  99,-1,-2,  99,-3,-4,
            99,99,99,  99,-5,-6,  99,-7,-8,
        ];
        $buffer->read($queue,$hostBuffer);
        foreach($trues as $idx => $value) {
            $this->assertTrue($hostBuffer[$idx] == $value);
        }
      
    }

    /**
     * copyRect
     */
    public function testCopyRect()
    {
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $ocl = $this->newDriverFactory();
        $context = $this->newContextFromType($ocl);
        $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
        $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
        // $dev_version = 'OpenCL 1.1 Mesa';
        $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;
        $queue = $ocl->CommandQueue($context);
        $newHostBufferFactory = $this->newHostBufferFactory();

        $hostBuffer = $newHostBufferFactory->Buffer(3*3*3,NDArray::float32);
        $data = [
            99,99,99,  99,99,99,  99,99,99,
            99,99,99,  99,-1,-2,  99,-3,-4,
            99,99,99,  99,-5,-6,  99,-7,-8,
        ];
        foreach($data as $idx => $value) {
            $hostBuffer[$idx] = $value;
        }
        $buffer = $ocl->Buffer(
            $context,
            $hostBuffer->value_size()*count($hostBuffer),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $hostBuffer
        );

        $subHostBuffer = $newHostBufferFactory->Buffer(2*2*2,NDArray::float32);
        $value_size = $subHostBuffer->value_size();
        $data = [
            0,0, 0,0,
            0,0, 0,0
        ];
        foreach($data as $idx => $value) {
            $subHostBuffer[$idx] = $value;
        }
        $dstBuffer = $ocl->Buffer(
            $context,
            $subHostBuffer->value_size()*count($subHostBuffer),
            OpenCL::CL_MEM_READ_WRITE|OpenCL::CL_MEM_COPY_HOST_PTR,
            $subHostBuffer
        );
        $dstBuffer->copyRect($queue,$buffer,[2*$value_size,2,2],
          $src_origin=[1*$value_size,1,1],
          $dst_origin=null,
          $src_row_pitch=$value_size*3,$src_slice_pitch=$value_size*3*3,
          $dst_row_pitch=$value_size*2,$dst_slice_pitch=$value_size*2*2,
          );
        $queue->finish();
        $dstBuffer->read($queue,$subHostBuffer);
        $trues = [
            -1,-2, -3,-4,
            -5,-6, -7,-8
        ];
        foreach($trues as $idx => $value) {
            $this->assertTrue($subHostBuffer[$idx] == $value);
        }
    }

    /**
     * get information
     */
    public function testGetInformation()
    {
        $ocl = $this->newDriverFactory();
        $platforms = $ocl->PlatformList();
        $devices = $ocl->DeviceList($platforms);
        $total_dev = $devices->count();
        $this->assertTrue($total_dev>=0);
        $mem_types = [
            0x10F0 => "CL_MEM_OBJECT_BUFFER",
            0x10F1 => "CL_MEM_OBJECT_IMAGE2D",
            0x10F2 => "CL_MEM_OBJECT_IMAGE3D",
            // CL_VERSION_1_2
            0x10F3 => "CL_MEM_OBJECT_IMAGE2D_ARRAY",
            0x10F4 => "CL_MEM_OBJECT_IMAGE1D",
            0x10F5 => "CL_MEM_OBJECT_IMAGE1D_ARRAY",
            0x10F6 => "CL_MEM_OBJECT_IMAGE1D_BUFFER",
        ];

        foreach([OpenCL::CL_DEVICE_TYPE_GPU,OpenCL::CL_DEVICE_TYPE_CPU] as $type) {
            try {
                $context = $ocl->Context($type);
            } catch(RuntimeException $e) {
                if(strpos('clCreateContextFromType',$e->getMessage())===null) {
                    throw $e;
                }
                $context = null;
            }
            if($context==null) {
                continue;
            }
            $this->assertTrue(1==$context->getInfo(OpenCL::CL_CONTEXT_REFERENCE_COUNT));

            $devices = $context->getInfo(OpenCL::CL_CONTEXT_DEVICES);
            $dev_version = $devices->getInfo(0,OpenCL::CL_DEVICE_VERSION);
            // $dev_version = 'OpenCL 1.1 Mesa';
            $isOpenCL110 = strstr($dev_version,'OpenCL 1.1') !== false;

            $buffer = $ocl->Buffer(
                $context,
                16,
                OpenCL::CL_MEM_READ_WRITE,
            );
            if($this->skipDisplayInfo) {
                return;
            }
            echo "========\n";
            echo "DeviceType: ".(($type===OpenCL::CL_DEVICE_TYPE_GPU)?'GPU':'CPU')."\n";
            echo "CL_MEM_TYPE=".$mem_types[$buffer->getInfo(OpenCL::CL_MEM_TYPE)]."\n";
            echo "CL_MEM_FLAGS=(";
            $mem_flags = $buffer->getInfo(OpenCL::CL_MEM_FLAGS);
            if($mem_flags&OpenCL::CL_MEM_READ_WRITE) { echo "READ_WRITE,"; }
            if($mem_flags&OpenCL::CL_MEM_WRITE_ONLY) { echo "WRITE_ONLY,"; }
            if($mem_flags&OpenCL::CL_MEM_READ_ONLY) { echo "READ_ONLY,"; }
            if($mem_flags&OpenCL::CL_MEM_USE_HOST_PTR) { echo "USE_HOST_PTR,"; }
            if($mem_flags&OpenCL::CL_MEM_ALLOC_HOST_PTR) { echo "ALLOC_HOST_PTR,"; }
            if($mem_flags&OpenCL::CL_MEM_COPY_HOST_PTR) { echo "COPY_HOST_PTR,"; }
            if(!$isOpenCL110) {
                if($mem_flags&OpenCL::CL_MEM_HOST_WRITE_ONLY) { echo "HOST_WRITE_ONLY,"; }
                if($mem_flags&OpenCL::CL_MEM_HOST_READ_ONLY) { echo "HOST_READ_ONLY,"; }
                if($mem_flags&OpenCL::CL_MEM_HOST_NO_ACCESS) { echo "HOST_NO_ACCESS,"; }
            }
            echo ")\n";
            echo "CL_MEM_SIZE=".$buffer->getInfo(OpenCL::CL_MEM_SIZE)."\n";
            echo "CL_MEM_MAP_COUNT=".$buffer->getInfo(OpenCL::CL_MEM_MAP_COUNT)."\n";
            echo "CL_MEM_REFERENCE_COUNT=".$buffer->getInfo(OpenCL::CL_MEM_REFERENCE_COUNT)."\n";
            echo "CL_MEM_OFFSET=".$buffer->getInfo(OpenCL::CL_MEM_OFFSET)."\n";
            //echo "CL_MEM_CONTEXT=".$buffer->getInfo(OpenCL::CL_MEM_CONTEXT)."\n";
            //echo "CL_MEM_ASSOCIATED_MEMOBJECT=".$buffer->getInfo(OpenCL::CL_MEM_ASSOCIATED_MEMOBJECT)."\n";
        }
    }
}
