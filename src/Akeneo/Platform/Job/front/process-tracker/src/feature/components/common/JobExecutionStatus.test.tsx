import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionStatus} from './JobExecutionStatus';
import {JobStatus} from '../../models';

test.each<JobStatus>(['COMPLETED', 'STOPPING', 'STOPPED', 'FAILED', 'ABANDONED', 'UNKNOWN'])(
  'It displays the job status "%s" without the progress',
  jobStatus => {
    renderWithProviders(
      <JobExecutionStatus status={jobStatus} currentStep={1} totalSteps={3} hasError={false} hasWarning={false} />
    );

    expect(screen.getByText(`akeneo_job.job_status.${jobStatus}`)).toBeInTheDocument();
  }
);

test.each<JobStatus>(['STARTING', 'IN_PROGRESS'])('It displays the job status "%s" with the progress', jobStatus => {
  const currentStep = 1;
  const totalSteps = 10;

  renderWithProviders(
    <JobExecutionStatus
      status={jobStatus}
      currentStep={currentStep}
      totalSteps={totalSteps}
      hasError={false}
      hasWarning={false}
    />
  );

  expect(screen.getByText(`akeneo_job.job_status.${jobStatus} ${currentStep}/${totalSteps}`)).toBeInTheDocument();
});

test('It displays the job status with level danger', () => {
  renderWithProviders(
    <JobExecutionStatus status={'IN_PROGRESS'} currentStep={1} totalSteps={10} hasError={true} hasWarning={false} />
  );

  // Since the level is only represented by the css generated by styled-components, it's currently impossible
  // to check it.
});

test('It displays the job status with level warning', () => {
  renderWithProviders(
    <JobExecutionStatus status={'IN_PROGRESS'} currentStep={1} totalSteps={10} hasError={false} hasWarning={true} />
  );

  // Since the level is only represented by the css generated by styled-components, it's currently impossible
  // to check it.
});
