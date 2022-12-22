---
# dL application documentation
## v1.0.1
### Symfony 3.4
### 02/11/21
---


# Entities

## Theme

A theme represents a political topic that is adressed by the state. The set of all the themes should implement a full partition of the political domain.

The themes are supposed to give a proper way to classify the proposals written by the users. Every published proposal is classified in a theme.

The themes are organized in a tree structure, as a folder system on an operating system. Each theme can host several children themes.

At the top of this hierarchic structure are themes that have no parent ($parent = null). Those specific themes are called roots.

Each theme hosts proposals that propose solutions for a better management of this topic.

It also hosts public discussions about the theme.

Only the administration is allowed to perform operations on the themes.

### Attributes (not exhaustive)

*$abtract* is a short text description of the theme

*$parent* is the parent theme that host the theme. It is null if the theme is a root.

*$children* is an array of the descendant themes

*$file* is the image associated to a theme

Some other arguments are used to manage the hierarchy relation. The tree architecture is implemented using DoctrineExtensions.

### Create a theme

The themes are created by the administration.

### Move a theme

The administration can move a theme.

The form will ask to choose either a parent theme, either the null value. The latter means that the theme will become a root.

### Delete a theme

A theme can be deleted only if it hosts no proposal.

In case the deleted theme has children, they will be reparent with the parent of the deleted theme.

If a theme is suppressed, all its public discussions will be suppressed from the database.



## Proposal

A proposal represents a political project. It is necessarily classified into a theme.

It contains a general motive and some articles that are written by the author.

The general motive gives the general idea of the project. It is written in the common language to be understandable by a large public.

The articles give the details of the project. They are written in the legal language. Each article is also motivated.

A proposal can also host public discussions. They can be opened by any logged user.

Starting from the first publication, a proposal is versioned. Each modification is saved. Anyone can view the history of a proposal.

### Attributes (not exhaustive)

*$status* There are 2 values possible for the status of a proposal: $PUBLISHED and $LOCKED. At creation, a proposal has the status $PUBLISHED, it is directly visible in the view of the theme and it can be edited by its author. The proposal can also be locked by the administration or the author. In this case it gets the $LOCKED status.

*$abstract* is a short text that sum up the project supported in the proposal. It should be under 400 characters.

*$motivation* is a text written in the common language that defend the project of the proposal.

*$supporters* The users that support the proposal

*$opponents* The users that are opposed to the proposal

*$versioning* is an array of the versions of the proposal (ProposalVersion entity) since its first publication. Each time the author modifies the proposal, a new ProposalVersion is created. A ProposalVersion entity represents a snapshot of the proposal at a given time

*$discussions* are the public discussions about the proposal

### Create a proposal
Any logged user can create a proposal through a theme view.

The proposal will then be classified in this theme and publicly visible.

The user becomes the author of the proposal.


### Edit a proposal
Editing a proposal consist in:

* changing its title
* changing its abstract
* changing its motivation

The edition of a proposal is only permitted to the author, not to the administration. And only if it is published.

The edition of a proposal will provoke its versioning.

### Supporters/opponents
Any logged user can support or contest a published proposal. He can then access the proposal from his profile.

### Lock a proposal
Locking a proposal will make it inactive: it will not be possible anymore to edit it, it will lock its articles and its public discussions, and it will not be possible to move it to an other theme.

It is not a reversible operation.

A short text to justify the locking should be given. It will be displayed under the title of the locked proposal.

This operation can be done by the author or the administration if the proposal is not already locked.

### Move a proposal
A proposal can be moved to a different theme.

Only the administration is allowed to do that action. If the propossal is published.

**So what happen if the administration wants to delete a theme where there are locked proposals? (this opened issue #113)**




## Article

A proposal, which represents a political project, is subdivided into articles for clarity and referencing, exactly as a law.

The Article entity represents an article hosted in a proposal. An article cannot exist independently.

### Attributes (not exhaustive)

*$status* There are 3 values possible for the status of an article: $PUBLISHED, $REMOVED and $LOCKED.

* At creation, an article has the status $PUBLISHED, it is directly visible in the view of the proposal and it can be edited.
* If at some point the author decides to discard it, it will get the status $REMOVED. It will not be visible by default in the view of a proposal, the user will have to choose the corresponding filter to see it. It will not be possible to interact with it (editing, creation of discussion…)
* If the author or the administration decide to lock the entire proposal, then the articles that had the status $PUBLISHED will have the status $LOCK. It will not be possible to interact with them anymore. But in the view of the locked proposal they will still be visible by default. It is when considering the locking of the proposal that one realises that the distinction between the $REMOVED and $LOCK status is necessary.

*$removingExplanation* When the author wants to remove an article, he is invited to justify his choice. This text is stored in this attribute.

*$proposal* is a Proposal entity. It represents the proposal that host the article. An article is always hosted in a proposal. This attribute cannot be null.

*$number* is an integer that stores the ordering of the article within the proposal. This ordering only makes sense among the articles that are not $REMOVED. It obviously makes sense among the articles of the proposal that are $PUBLISHED, but it still makes sense to keep the ordering between articles that are $LOCKED.

*$title* should not have more than 100 characters and at least one character among the ones between the brackets of the regex [a-zA-Z0-9]+

*$content* is the core of the article that is written in the legal language

*$motivation* is written in the common language and give arguments that support the content of the article

*$versionNumber$* is an integer that is incremented each time the article is modified


### Edit an article

Editing an article consist in:

* changing its title
* changing its content
* changing its motivation

The permission on editing an article is checked by the voter word CAN_BE_EDITED.

The edition of an article is possible only if it is published.

Only the author of the proposal can edit an article, the administration cannot.

Editing an article will provoke the versioning of the article, and of the proposal.

### Remove an article

An article can be removed by the author of the proposal only if the article is published.

Removing an article will prevent anyone to edit it.

Removing an article will lock its discussions.

Any user can read a removed article and its discussions.

Removing an article is not a reversible operation.

While removing an article, the author is invited to justify his choice. The justification will be displayed under the title of the removed article.

If an article is removed, the numbers of the following articles (if they exist) are decreased by 1. This way the numbering of the published articles is kept consistent. The versioning of those articles is updated.

If an article is removed the versioning of the proposal is updated. The removed article will not appear in the last version of the proposal.

### Lock an article

Locking an article is done if the author or the administration decide to lock the hosting proposal.

Locking an article will lock its edition and its discussions.

It will still be readable and ordered in the view of the locked proposal.

No versioning operation is performed.


## PublicDiscussion

This entity inherits from the AbstractDiscussion entity.

This entity represents an open discussion that anyone can read.

A public discussion hosts Post entities that represent the comments of the users.

### Attributes (not exhaustive)
*$type* The public discussions are classified into 4 types depending on the topic they address.

* a $GLOBAL_DISCUSSION type in accessible through the global room. The topic is free.
* $THEME_DISCUSSION associated to a particular theme
* $PROPOSAL_DISCUSSION
* $ARTICLE_DISCUSSION

*$followers* is an array of Users entities. It registers the users that follow the discussion. In that case they can access it directly through their account.

### Create a public discussion
Any logged user can create a public discussion through a theme, a proposal or article view, and also in the global room.

### Edit a public discussion
Edit a public discussion consists only in modifying its title.

**Only the administration can edit a public discussion?? (issue #115)**

### Follow a public discussion
Any logged user can choose to follow any public discussion. In this case the discussion will appear in the followed discussion tab of his profile.

### Post in a public discussion
Any logged user can post in a public discussion.
He can post an empty comment.

### Move a public discussion
The administration can move a public discussion and also change its type. It can for exemple move a global discussion to a theme discussion.

### Lock a public discussion
The administration can lock a public discussion. In this case the $locked attribute is set to True. The discussion is inactive: no posts are accepted and the user should choose the proper filter to read it.

This action is reversible.

### Delete a public discussion
**Only the administration can remove a public discussion from the database only if it is published (issue #116)**


## PrivateDiscussion

Inherits from AbstractDiscussion.

This entity represents a private discussion between some users.

### Attributes (not exhaustive)
*$admin* is the administrator of the discussion. He has special rights.

*$members* is an array of Users entities. Only them can read and post in the discussion. The admin is always among the members.

### Create a private discussion
Any logged user can create a private discussion through its profile.

He can choose the members he likes.

The creator of the discussion is by default the admin.

### Follow a private discussion

The members can access the discussion through their personal profile.

It is signaled when there has been some new posts (the members are then placed in PrivateDiscussion.unreaders). The discussion is highlighted.

### Edit a private discussion
The change of the title can be done by the admin.

### Lock a private discussion
Locking the discussion can be done by the admin.

This is reversible.

### Add/remove a member to a private discussion
Can be done by the admin.

### Change the admin of a private discussion
The admin can choose another member to replace him.

### Delete a private discussion
The admin can remove the discussion from the database.