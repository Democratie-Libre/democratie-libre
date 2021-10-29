---
# dL application documentation
## v1.0
### Symfony 3.4
### 29/10/21
---

# Entities

## Article

A proposal, which represents a political project, is subdivided into articles for clarity and referencing, exactly as a law.

The Article entity represents an article in a proposal.

An article cannot exist independently.

### Attributes

*$status*

*$proposal* is a Proposal entity. It represents the proposal that host the article. An article is always hosted in a proposal. This attribute cannot be null.

### Edit an article

The edition of an article consist in:

* changing its title
* changing its content
* changing its motivation

The permission on editing an article is checked by the voter word CAN_BE_EDITED.

The edition of an article is possible only if it is published.

Only the author of the proposal can edit an article, the administration cannot.

### Lock an article

Only the author of the proposal can lock an article.

Locking an article will prevent anyone to edit it.

Locking an article will lock its discussions.

Any user can read a locked article and its discussions.

Locking an article is not a reversible operation.

While locking an article, the author is invited to justify his choice. The justification will be displayed under the title of the locked article.

If an article is locked, the numbers of the following articles (if they exist) are decreased by 1. This way the numbering of the published articles is kept consistent. The versioning of those articles is updated.

If an article is locked the versioning of the proposal is updated. The locked article will not appear in the last version of the proposal.


