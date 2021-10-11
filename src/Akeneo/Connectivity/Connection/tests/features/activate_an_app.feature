@ce @optional @javascript
Feature: Activate an OAuth2 client application in the PIM
  In order to activate an App
  As Julia
  I need to be able to go through the Authorization tunnel

  Scenario: julia is authorized to activate an App
    Given a "default" catalog configuration
    And the role "ROLE_CATALOG_MANAGER" has the ACL "Manage Apps"
    And the user "Julia" has the profile "product_manager"
    And I am logged in as "Julia"
    And I am on the marketplace page
    And I should see "Yell extension" app
    When I click on "Yell extension" activate button
    And the url matches "https://yell-extension-t2omu7tdaq-uc.a.run.app/activate?pim_url=http%3A%2F%2Fhttpd"
    And I click on the button "Connect Yell to the PIM"
    And the url matches "http://httpd/#/connect/apps/authorize?client_id=6ff52991-1144-45cf-933a-5c45ae58e71a"
    And I see "View products and product models"
    And I click on the button "Confirm"
    Then I have the connected app "Yell extension"
    And my connected app has the following ACLs:
      | name                         | enabled |
      | pim_api_overall_access       | true    |
      | pim_api_product_list         | true    |
      | pim_api_product_edit         | false   |
      | pim_api_product_remove       | false   |
      | pim_api_attribute_list       | true    |
      | pim_api_attribute_group_list | true    |
      | pim_api_family_list          | true    |
      | pim_api_family_variant_list  | true    |
      | pim_api_attribute_edit       | false   |
      | pim_api_attribute_group_edit | false   |
      | pim_api_family_edit          | false   |
      | pim_api_family_variant_edit  | false   |
    And it has an API token