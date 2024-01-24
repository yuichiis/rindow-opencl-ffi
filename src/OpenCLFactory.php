<?php
namespace Rindow\OpenCL\FFI;

use FFI;
use FFI\Env\Runtime as FFIEnvRuntime;
use FFI\Env\Status as FFIEnvStatus;
use FFI\Location\Locator as FFIEnvLocator;
use Interop\Polite\Math\Matrix\LinearBuffer as HostBuffer;

class OpenCLFactory
{
    private static ?FFI $ffi = null;
    protected array $libs = ['OpenCL.dll','libOpenCL.so.1'];

    public function __construct(
        string $headerFile=null,
        array $libFiles=null,
        )
    {
        if(self::$ffi!==null) {
            return;
        }
        //$this->assertExistLibrary('');
        $headerFile = $headerFile ?? __DIR__ . "/opencl_win.h";
        $libFiles = $libFiles ?? $this->libs;
        //$ffi = FFI::load($headerFile);
        $code = file_get_contents($headerFile);
        $pathname = FFIEnvLocator::resolve(...$libFiles);
        if($pathname) {
            $ffi = FFI::cdef($code,$pathname);
            self::$ffi = $ffi;
        }
    }

    public function isAvailable() : bool
    {
        $isAvailable = FFIEnvRuntime::isAvailable();
        if(!$isAvailable) {
            return false;
        }
        $pathname = FFIEnvLocator::resolve(...$this->libs);
        return $pathname!==null;
    }

    public function PlatformList() : PlatformList
    {
        return new PlatformList(self::$ffi);
    }

    public function DeviceList(
        PlatformList $platforms,
        int $index=NULL,
        int $deviceType=NULL,
    ) : DeviceList
    {
        return new DeviceList(self::$ffi,$platforms,$index,$deviceType);
    }

    public function Context(
        DeviceList|int $arg
    ) : Context
    {
        return new Context(self::$ffi,$arg);
    }

    public function EventList(
        Context $context=null
    ) : EventList
    {
        return new EventList(self::$ffi, $context);
    }

    public function CommandQueue(
        Context $context,
        object $deviceId=null,
        object $properties=null,
    ) : CommandQueue
    {
        return new CommandQueue(self::$ffi, $context, $deviceId, $properties);
    }

    public function Program(
        Context $context,
        string|array $source,   // string or list of something
        int $mode=null,         // mode  0:source codes, 1:binary, 2:built-in kernel, 3:linker
        DeviceList $deviceList=null,
        string $options=null,
        ) : Program
    {
        return new Program(self::$ffi, $context, $source, $mode, $deviceList, $options);
    }

    public function Buffer(
        Context $context,
        int $size,
        int $flags=null,
        HostBuffer $hostBuffer=null,
        int $hostOffset=null,
        int $dtype=null,
        ) : Buffer
    {
        return new Buffer(self::$ffi, $context, $size, $flags, $hostBuffer, $hostOffset, $dtype);
    }

    public function Kernel
    (
        Program $program,
        string $kernelName,
        ) : Kernel
    {
        return new Kernel(self::$ffi, $program, $kernelName);
    }
}
