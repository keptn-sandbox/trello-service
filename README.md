# Keptn Trello Service
This Keptn Service integrates Atlassian Trello with Keptn.

This keptn service creates items on Trello boards when a keptn evaluation (`sh.keptn.event.start-evaluation`) is performed. The service subscribes to the following keptn events:

* `sh.keptn.events.evaluation-done`

# Debugging
A debug log is available in the `trello-service` pod at `/var/www/html/logs/trelloService.log`

```
kubectl exec -itn keptn trello-service-*-* cat /var/www/html/logs/trelloService.log
```

# Compatibility Matrix

| Keptn Version    | Trello API Version |
|:----------------:|:----------------------:|
|     0.6.1        |            v1          |

# Contributions, Enhancements, Issues or Questions
Please raise a GitHub issue or join the [Keptn Slack channel](https://join.slack.com/t/keptn/shared_invite/enQtNTUxMTQ1MzgzMzUxLWMzNmM1NDc4MmE0MmQ0MDgwYzMzMDc4NjM5ODk0ZmFjNTE2YzlkMGE4NGU5MWUxODY1NTBjNjNmNmI1NWQ1NGY).
