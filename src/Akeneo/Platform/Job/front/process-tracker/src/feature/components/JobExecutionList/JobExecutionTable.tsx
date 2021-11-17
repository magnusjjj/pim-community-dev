import React, {useCallback, MouseEvent} from 'react';
import {Table} from 'akeneo-design-system';
import {useDateFormatter, useTranslate} from '@akeneo-pim-community/shared';
import {JobExecutionRow, JobExecutionFilterSort} from '../../models';
import {JobExecutionStatus} from '../JobExecutionStatus';
import {useHistory} from 'react-router-dom';

type JobExecutionTableProps = {
  sticky?: number;
  jobExecutionRows: JobExecutionRow[];
  onSortChange: (sort: JobExecutionFilterSort) => void;
  currentSort: JobExecutionFilterSort;
};

const SORTABLE_COLUMN_HEADERS = ['job_name', 'type', 'started_at', 'username', 'status'];

const JobExecutionTable = ({sticky, jobExecutionRows, onSortChange, currentSort}: JobExecutionTableProps) => {
  const translate = useTranslate();
  const dateFormatter = useDateFormatter();
  const sortDirection = 'ASC' === currentSort.direction ? 'ascending' : 'descending';
  const history = useHistory();
  const handleRowClick = useCallback(
    (jobExecutionId: number) => (event: MouseEvent<HTMLTableRowElement>) => {
      const url = `/show/${jobExecutionId}`;
      if (event.metaKey || event.ctrlKey) {
        const newTab = window.open(`${window.location.hash}${url}`, '_blank');
        newTab?.focus();
        return;
      }

      history.push(url);
    },
    [history]
  );

  return (
    <Table>
      <Table.Header sticky={sticky}>
        {SORTABLE_COLUMN_HEADERS.map(sortableColumnHeader => (
          <Table.HeaderCell
            key={sortableColumnHeader}
            isSortable={true}
            onDirectionChange={direction => {
              if ('none' !== direction) {
                onSortChange({column: sortableColumnHeader, direction: 'ascending' === direction ? 'ASC' : 'DESC'});
              }
            }}
            sortDirection={currentSort.column === sortableColumnHeader ? sortDirection : 'none'}
          >
            {translate(`akeneo_job_process_tracker.job_execution_list.table.headers.${sortableColumnHeader}`)}
          </Table.HeaderCell>
        ))}
        <Table.HeaderCell>
          {translate(`akeneo_job_process_tracker.job_execution_list.table.headers.warning_count`)}
        </Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        {jobExecutionRows.map(jobExecutionRow => (
          <Table.Row key={jobExecutionRow.job_execution_id} onClick={handleRowClick(jobExecutionRow.job_execution_id)}>
            <Table.Cell rowTitle={true}>{jobExecutionRow.job_name}</Table.Cell>
            <Table.Cell>
              {translate(`pim_import_export.widget.last_operations.job_type.${jobExecutionRow.type}`)}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.started_at
                ? dateFormatter(jobExecutionRow.started_at, {
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                  })
                : '-'}
            </Table.Cell>
            <Table.Cell>{jobExecutionRow.username}</Table.Cell>
            <Table.Cell>
              <JobExecutionStatus
                status={jobExecutionRow.status}
                hasWarning={jobExecutionRow.warning_count > 0}
                hasError={jobExecutionRow.error_count > 0}
                currentStep={jobExecutionRow.tracking.current_step}
                totalSteps={jobExecutionRow.tracking.total_step}
              />
            </Table.Cell>
            <Table.Cell>{jobExecutionRow.warning_count > 0 ? jobExecutionRow.warning_count : '-'}</Table.Cell>
          </Table.Row>
        ))}
      </Table.Body>
    </Table>
  );
};

export {JobExecutionTable};