import PropTypes from 'prop-types';
import React from "react";
import gql from "graphql-tag";
import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import { Query } from "react-apollo";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";
import postGql from 'raw-loader!../../raw/graphql/post.graphql';

const postQuery = (id) => {
  return gql(postGql.replace('__POST_ID__', id));
}

const styles = theme => ({
  wrap: {
    padding: '5px',
    backgroundColor:'white',
  },
});

class Post extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
    };
  }

  renderPost(post) {
    const { classes } = this.props;

    return (
      <div className={classes.wrap}>
        <br/>
        <br/>
        { post.title }<br />
        { post.description }
      </div>
    );
  }

  render() {

    const id = this.props.match.params.id;
    const {initialProps} = this.props;

    let post = false;
    if (  (initialProps.data) &&
          (initialProps.data.post) &&
          (id == initialProps.data.post.id)) {

      post = initialProps.data.post;
    }

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        { ( post ) ?
          <div>
            { this.renderPost(post) }
          </div> :

          <Query
            query={postQuery(id)} >

            {({ loading, error, data }) => {
              if (loading) return <Loading />;
              if (error) return <p>Error </p>;

              return this.renderPost(data.post);
            }}

          </Query>
        }
      </div>
    );
  }
}

export default withStyles(styles)(Post);
