
import gql from "graphql-tag";
import React from "react";
import PropTypes from 'prop-types';
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { withStyles } from '@material-ui/core/styles';
import { Query } from "react-apollo";

import Loading from "../Trim/Loading";
import * as Constants from '../../constants'
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

  componentDidMount() {
    console.log('did mount');
  }

  render() {

    const id = this.props.match.params.id;
    const { classes } = this.props;

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        <Query
          query={gql`
            {
              posts(first: 20) {
                edges {
                  node {
                    id
                    active
                    pinned
                    title
                    slug
                    year
                    image
                    image_x
                    image_y
                    description
                    lead
                    fold
                    published_at
                    user {
                      first_name
                      last_name
                    }
                  }
                }
              }
            }
          `}
        >

          {({ loading, error, data }) => {
            if (loading) return <Loading />;
            if (error) return <p>Error </p>;

            return (
              <div className={classes.wrap}>

                {data.posts.edges.map((post, index) => (
                    <div key={post.node.id} >
                      <Link to={generatePostLink(post.node)} >
                        <strong>{post.node.title}</strong>
                      </Link>
                    </div>
                ))}

              </div>
            );
          }}
        </Query>

      </div>
    );
  }
}

export default withStyles(styles)(Blog);
