<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Renderer;

use Dompdf\Dompdf;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Renderer as DrupalRenderer;
use Drupal\hoeringsportal_citizen_proposal_archiving\Exception\RuntimeException;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Renderer for citizen proposal archiving.
 */
final class Renderer implements LoggerAwareInterface, LoggerInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private DrupalRenderer $renderer,
    readonly private ModuleHandlerInterface $moduleHandler,
  ) {
  }

  /**
   * Render node as PDF.
   */
  public function renderPdf(NodeInterface $node, array $context = []): string {
    $html = $this->renderHtml($node, $context);

    $dompdf = new Dompdf();
    $dompdf->getOptions()
      ->setIsRemoteEnabled(TRUE)
      ->setIsFontSubsettingEnabled(TRUE)
      ->setChroot($this->getTemplateDirectory());
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
  }

  /**
   * Render node as HTML.
   */
  public function renderHtml(NodeInterface $node, array $context = []): string {
    $templatePath = $this->getTemplateDirectory() . '/citizen_proposal.html.twig';
    if (!file_exists($templatePath)) {
      throw new RuntimeException(sprintf('Cannot load template %s', $templatePath));
    }
    $template = file_get_contents($templatePath) ?: NULL;
    if (NULL === $template) {
      throw new RuntimeException(sprintf('Cannot load template %s', $templatePath));
    }

    $build = [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => $context + [
        'template_url' => 'file://' . $templatePath,
        'node' => $node,
      ],
    ];

    return trim((string) $this->renderer->renderInIsolation($build));
  }

  /**
   * Get template directory.
   */
  private function getTemplateDirectory(): string {
    $modulePath = $this->moduleHandler->getModule('hoeringsportal_citizen_proposal_archiving')->getPath();

    return realpath($modulePath . '/templates');
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
