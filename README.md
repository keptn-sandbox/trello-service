# Keptn Trello Service
This Keptn Service integrates Atlassian Trello with Keptn.

This keptn service creates items on Trello boards when a keptn evaluation (`sh.keptn.event.start-evaluation`) is performed. The service subscribes to the following keptn events:

* `sh.keptn.events.evaluation-done`
