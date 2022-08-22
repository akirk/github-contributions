import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Icon, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const GithubIcon = () => (
    <Icon
        icon={ () => (
            <svg>
                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385c.6.105.825-.255.825-.57c0-.285-.015-1.23-.015-2.235c-3.015.555-3.795-.735-4.035-1.41c-.135-.345-.72-1.41-1.23-1.695c-.42-.225-1.02-.78-.015-.795c.945-.015 1.62.87 1.845 1.23c1.08 1.815 2.805 1.305 3.495.99c.105-.78.42-1.305.765-1.605c-2.67-.3-5.46-1.335-5.46-5.925c0-1.305.465-2.385 1.23-3.225c-.12-.3-.54-1.53.12-3.18c0 0 1.005-.315 3.3 1.23c.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23c.66 1.65.24 2.88.12 3.18c.765.84 1.23 1.905 1.23 3.225c0 4.605-2.805 5.625-5.475 5.925c.435.375.81 1.095.81 2.22c0 1.605-.015 2.895-.015 3.3c0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12Z" />
            </svg>
        ) }
    />
);
registerBlockType( 'akirk/github-contributions', {
	apiVersion: 2,
	icon: GithubIcon,
	edit: function( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		return (
			<>
				<InspectorControls>
					<PanelBody>
						<TextControl
							label={ __( 'Username', 'github-contributions' ) }
							value={ attributes.username }
							onChange={ username => setAttributes( { username } ) }
						/>
						<SelectControl
							label={ __( 'Sort', 'github-contributions' ) }
							onChange={ sort => setAttributes( { sort } ) }
							value={ attributes.sort }
							options={ [
								{
									label: __( 'date', 'github-contributions' ),
									value: 'date',
								},
								{
									label: __( 'count', 'github-contributions' ),
									value: 'count',
								},
							] }
						/>
						<SelectControl
							label={ __( 'Limit', 'github-contributions' ) }
							onChange={ limit => setAttributes( { limit } ) }
							value={ attributes.limit }
							options={ [
								{
									label: 10,
									value: 10,
								},
								{
									label: 20,
									value: 20,
								},
								{
									label: 100,
									value: 100,
								},
							] }
						/>
					</PanelBody>
				</InspectorControls>
				<div {...blockProps}>
					<ServerSideRender
					block="akirk/github-contributions"
						attributes={ attributes }
					/>
				</div>
			</>
		);
	},

    save() {
    	return null;
    },
} );
