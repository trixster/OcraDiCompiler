<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace OcraDiCompiler;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\EventManager\Event;
use Zend\Loader\StandardAutoloader;

use Zend\Di\Di;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

use OcraDiCompiler\Service\CompiledDiFactory;

/**
 * Module that overrides Di factory with a compiled Di factory. That allows great performance improvements.
 * It lazily checks if a compiled Di file/class was found/defined.. If set, uses it to replace the Di in the
 * ServiceManager.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(Event $e)
    {
        /* @var $application \Zend\Mvc\ApplicationInterface */
        $application = $e->getTarget();
        /* @var $sm \Zend\ServiceManager\ServiceManager */
        $sm = $application->getServiceManager();

        if (!$sm instanceof ServiceManager) {
            return;
        }

        $this->overrideDiFactory($sm);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * @param ServiceManager $sm
     */
    protected function overrideDiFactory(ServiceManager $sm)
    {
        $allowOverride = $sm->getAllowOverride();
        $sm->setAllowOverride(true);
        $sm->setFactory('DependencyInjector', new CompiledDiFactory());
        $sm->setAllowOverride($allowOverride);
    }
}