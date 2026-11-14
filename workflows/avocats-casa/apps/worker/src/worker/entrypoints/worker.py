import asyncio

import mistralai.workflows as workflows_client


def main() -> None:
    workflow_classes = workflows_client.discover_all_workflows_in_package("worker.workflows")
    asyncio.run(workflows_client.run_worker(workflow_classes))


if __name__ == "__main__":
    main()
