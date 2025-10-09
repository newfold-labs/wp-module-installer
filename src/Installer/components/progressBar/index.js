import { memo } from '@wordpress/element';

export const ProgressBar = memo( ( { completed, total } ) => {
	const percent = total ? Math.round( ( completed / total ) * 100 ) : 0;
	return (
		<div className={ `nfd-progress-bar nfd-progress-bar-${ percent }` }>
			<div
				className="nfd-progress-bar-inner"
				data-percent={ percent }
				style={ { width: `${ percent }%` } }
			/>
		</div>
	);
} );
