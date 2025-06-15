<?php

namespace App\EventListener;

use App\Exception\Handler\ExceptionMapping;
use App\Exception\Handler\ExceptionMappingResolver;
use App\Response\ErrorDebugDetails;
use App\Response\ErrorResponse;
use App\Response\ErrorWoutDetailsResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

readonly class OnExceptionListener
{
    public function __construct(
        private ExceptionMappingResolver $resolver,
        private LoggerInterface $logger,
        private SerializerInterface $serializer,
        private bool $isDebug
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($this->isSecurityException($throwable)) {
            return;
        }

        $mapping = $this->resolver->resolve(get_class($throwable));

        if (null === $mapping) {
            $mapping = ExceptionMapping::fromCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //dd($event->getThrowable());

        if ($event->getThrowable() instanceof AccessDeniedException) {
            $data = $this->serializer->serialize(new ErrorWoutDetailsResponse('Access Denied'), JsonEncoder::FORMAT);

            $code = JsonResponse::HTTP_FORBIDDEN;
        } else {
            if ($mapping->getCode() >= Response::HTTP_INTERNAL_SERVER_ERROR || $mapping->isLoggable()) {
                $this->logger->error($throwable->getMessage(), [
                    'trace' => $throwable->getTraceAsString(),
                    'previous' => null !== $throwable->getPrevious() ? $throwable->getPrevious()->getMessage() : '',
                ]);
            }

            $message = $mapping->isHidden() ? Response::$statusTexts[$mapping->getCode()] : $throwable->getMessage();

            $code = $mapping->getCode();

            if ($this->isDebug) {
                $details = new ErrorDebugDetails($throwable->getTraceAsString());

                $data = $this->serializer->serialize(new ErrorResponse($message, $details), JsonEncoder::FORMAT);
            } else {
                $data = $this->serializer->serialize(new ErrorWoutDetailsResponse($message), JsonEncoder::FORMAT);
            }
        }

        $event->setResponse(new JsonResponse($data, $code, [], true));
    }

    private function isSecurityException(\Throwable $throwable): bool
    {
        return $throwable instanceof AuthenticationException;
    }
}
