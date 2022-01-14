<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\ViewHelpers;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Category;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\ViewHelpers\ConvertToJsonViewHelper;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * Class ConvertToJsonViewHelper
 */
class ConvertToJsonViewHelperTest extends FunctionalTestCase
{
    /**
     * @var RenderingContext|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $renderingContext;

    /**
     * @var ConvertToJsonViewHelper
     */
    protected $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/maps2'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderingContext = $this->prophesize(RenderingContext::class);

        $this->subject = new ConvertToJsonViewHelper();
        $this->subject->setRenderingContext($this->renderingContext->reveal());
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWithStringWillJustCallJsonEncode(): void
    {
        $this->subject->setRenderChildrenClosure(
            fn(): string => 'simpleString'
        );

        self::assertSame(
            '&quot;simpleString&quot;',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithSimpleArrayWillJustCallJsonEncode(): void
    {
        $this->subject->setRenderChildrenClosure(
            fn(): array => ['foo' => 'bar']
        );

        self::assertSame(
            '{&quot;foo&quot;:&quot;bar&quot;}',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionWillSetItToArrayAndConvertItToJson(): void
    {
        $this->subject->setRenderChildrenClosure(
            fn(): PoiCollection => new PoiCollection()
        );

        GeneralUtility::setSingletonInstance(ExtConf::class, new ExtConf([]));

        $json = $this->subject->render();

        // a property of PoiCollection should be found in string
        self::assertContains(
            'address',
            $json
        );

        // we have set PoiCollection into an array, so JSON should start with [{
        self::stringStartsWith('[{');
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillConvertItToJson(): void
    {
        $this->subject->setRenderChildrenClosure(
            fn(): array => [new PoiCollection()]
        );

        $json = $this->subject->render();

        // a property of PoiCollection should be found in string
        self::assertContains(
            'address',
            $json
        );

        // we have set PoiCollection into an array, so JSON should start with [{
        self::stringStartsWith('[{');
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillRemoveMaps2MarkerIconsFromCategories(): void
    {
        $poiCollection = new PoiCollection();
        $poiCollection->addCategory(new Category());

        $this->subject->setRenderChildrenClosure(
            fn(): array => [$poiCollection]
        );

        $json = $this->subject->render();

        self::assertNotContains(
            'maps2MarkerIcons',
            $json
        );
        self::assertNotContains(
            'parent',
            $json
        );
    }

    /**
     * @test
     */
    public function renderWithPoiCollectionsWillRemoveMarkerIconsFromPoiCollection(): void
    {
        $poiCollection = new PoiCollection();

        $this->subject->setRenderChildrenClosure(
            fn(): array => [$poiCollection]
        );

        $json = $this->subject->render();

        self::assertNotContains(
            'markerIcons',
            $json
        );
    }
}
