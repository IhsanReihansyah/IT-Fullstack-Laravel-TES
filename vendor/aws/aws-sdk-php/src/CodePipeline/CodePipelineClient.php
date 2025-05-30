<?php
namespace Aws\CodePipeline;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CodePipeline** service.
 *
 * @method \Aws\Result acknowledgeJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise acknowledgeJobAsync(array $args = [])
 * @method \Aws\Result acknowledgeThirdPartyJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise acknowledgeThirdPartyJobAsync(array $args = [])
 * @method \Aws\Result createCustomActionType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createCustomActionTypeAsync(array $args = [])
 * @method \Aws\Result createPipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \Aws\Result deleteCustomActionType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteCustomActionTypeAsync(array $args = [])
 * @method \Aws\Result deletePipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \Aws\Result deleteWebhook(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWebhookAsync(array $args = [])
 * @method \Aws\Result deregisterWebhookWithThirdParty(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterWebhookWithThirdPartyAsync(array $args = [])
 * @method \Aws\Result disableStageTransition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableStageTransitionAsync(array $args = [])
 * @method \Aws\Result enableStageTransition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableStageTransitionAsync(array $args = [])
 * @method \Aws\Result getActionType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getActionTypeAsync(array $args = [])
 * @method \Aws\Result getJobDetails(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getJobDetailsAsync(array $args = [])
 * @method \Aws\Result getPipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPipelineAsync(array $args = [])
 * @method \Aws\Result getPipelineExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPipelineExecutionAsync(array $args = [])
 * @method \Aws\Result getPipelineState(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPipelineStateAsync(array $args = [])
 * @method \Aws\Result getThirdPartyJobDetails(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getThirdPartyJobDetailsAsync(array $args = [])
 * @method \Aws\Result listActionExecutions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listActionExecutionsAsync(array $args = [])
 * @method \Aws\Result listActionTypes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listActionTypesAsync(array $args = [])
 * @method \Aws\Result listDeployActionExecutionTargets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeployActionExecutionTargetsAsync(array $args = [])
 * @method \Aws\Result listPipelineExecutions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPipelineExecutionsAsync(array $args = [])
 * @method \Aws\Result listPipelines(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \Aws\Result listRuleExecutions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRuleExecutionsAsync(array $args = [])
 * @method \Aws\Result listRuleTypes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRuleTypesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listWebhooks(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWebhooksAsync(array $args = [])
 * @method \Aws\Result overrideStageCondition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise overrideStageConditionAsync(array $args = [])
 * @method \Aws\Result pollForJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise pollForJobsAsync(array $args = [])
 * @method \Aws\Result pollForThirdPartyJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise pollForThirdPartyJobsAsync(array $args = [])
 * @method \Aws\Result putActionRevision(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putActionRevisionAsync(array $args = [])
 * @method \Aws\Result putApprovalResult(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putApprovalResultAsync(array $args = [])
 * @method \Aws\Result putJobFailureResult(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putJobFailureResultAsync(array $args = [])
 * @method \Aws\Result putJobSuccessResult(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putJobSuccessResultAsync(array $args = [])
 * @method \Aws\Result putThirdPartyJobFailureResult(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putThirdPartyJobFailureResultAsync(array $args = [])
 * @method \Aws\Result putThirdPartyJobSuccessResult(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putThirdPartyJobSuccessResultAsync(array $args = [])
 * @method \Aws\Result putWebhook(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putWebhookAsync(array $args = [])
 * @method \Aws\Result registerWebhookWithThirdParty(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerWebhookWithThirdPartyAsync(array $args = [])
 * @method \Aws\Result retryStageExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise retryStageExecutionAsync(array $args = [])
 * @method \Aws\Result rollbackStage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise rollbackStageAsync(array $args = [])
 * @method \Aws\Result startPipelineExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startPipelineExecutionAsync(array $args = [])
 * @method \Aws\Result stopPipelineExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopPipelineExecutionAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateActionType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateActionTypeAsync(array $args = [])
 * @method \Aws\Result updatePipeline(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updatePipelineAsync(array $args = [])
 */
class CodePipelineClient extends AwsClient {}