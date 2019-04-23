import PropTypes from 'prop-types';
import React from "react";
import gql from "graphql-tag";
import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import { Query } from "react-apollo";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";
import postsGql from 'raw-loader!../../raw/graphql/posts.graphql';
import { generatePostLink } from "../../util.js";

const styles = theme => ({
  wrap: {
    padding: '5px',
    backgroundColor:'white',
    minHeight: 'calc(100vh - 56px)',
  },
  head: {
  }
});

class Blog extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
    };
  }

  renderPosts(posts) {
    const { classes } = this.props;

    return (
      <div className={classes.wrap}>

        {posts.edges.map((post, index) => (
            <div key={post.node.id} >
              <Link to={generatePostLink(post.node)} >
                <strong>{post.node.title}</strong>
              </Link>
            </div>
        ))}

      </div>
    );
  }

  render() {

    const id = this.props.match.params.id;
    const { initialProps } = this.props;

    let posts = false;
    if (  (initialProps.data) &&
          (initialProps.data.posts)) {

      posts = initialProps.data.posts;
    }

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        { ( posts ) ?
          <div>
            { this.renderPosts(posts) }
          </div> :
          <Query query={gql(postsGql)} >

            {({ loading, error, data }) => {
              if (loading) return <Loading />;
              if (error) return <p>Error </p>;

              return this.renderPosts(data.posts);
            }}
          </Query>
        }

      </div>
    );
  }
}

export default withStyles(styles)(Blog);
