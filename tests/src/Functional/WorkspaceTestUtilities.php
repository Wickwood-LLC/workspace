<?php
/**
 * @file
 * Contains WorkspaceTestUtilities.php
 */


namespace Drupal\Tests\workspace\Functional;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;

/**
 * Utility methods for use in BrowserTestBase tests.
 *
 * This trait will not work if not used in a child of BrowserTestBase.
 */
trait WorkspaceTestUtilities {

  /**
   * Loads a single workspace by its label.
   *
   * The UI approach to creating a workspace doesn't make it easy to know what
   * the ID is, so this lets us make paths for a workspace after it's created.
   *
   * @param $label
   *   The label of the workspace to load.
   * @return WorkspaceInterface
   */
  protected function getOneWorkspaceByLabel($label) {
    /** @var EntityTypeManagerInterface $etm */
    $etm = \Drupal::service('entity_type.manager');

    /** @var WorkspaceInterface $bears */
    $entity_list = $etm->getStorage('workspace')->loadByProperties(['label' => $label]);
    return current($entity_list);
  }

  /**
   * Creates a new Workspace through the UI.
   *
   * @param string $label
   *   The label of the workspace to create.
   * @param string $machine_name
   *   The machine name of the workspace to create.
   *
   * @return WorkspaceInterface
   *   The workspace that was just created.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function createWorkspaceThroughUI($label, $machine_name) {
    $this->drupalGet('/admin/structure/workspace/add');

    $session = $this->getSession();
    $this->assertEquals(200, $session->getStatusCode());

    $page = $session->getPage();
    $page->fillField('label', $label);
    $page->fillField('machine_name', $machine_name);
    $page->findButton(t('Save'))->click();

    $session->getPage()->hasContent("$label ($machine_name)");

    return $this->getOneWorkspaceByLabel($label);
  }

}
